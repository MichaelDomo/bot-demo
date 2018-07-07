<?php

namespace app\components\bot\services;

use app\components\bot\repositories\FreelanceProjectRepository;
use app\components\bot\repositories\UserBookmarkRepository;
use app\components\bot\repositories\UserBotProviderRepository;
use app\components\bot\repositories\UserCodeRepository;
use app\components\bot\repositories\UserConversationRepository;
use app\components\bot\repositories\UserRepository;
use app\components\bot\repositories\UserSettingsRepository;
use app\models\user\bot\UserBotProvider;
use dektrium\user\models\Code as UserCode;
use app\models\user\bot\UserBotConversation;
use app\models\user\UserBookmark;
use Yii;

/**
 * Class ConversationService
 * @package app\components\bot
 */
class ConversationService
{
    private $userRepository;
    private $userCodeRepository;
    private $userBookmarkRepository;
    private $userConversationRepository;
    private $userBotProviderRepository;
    private $freelanceProjectRepository;
    private $userSettingsRepository;

    /**
     * ConversationService constructor.
     * @param UserRepository $userRepository
     * @param UserCodeRepository $userCodeRepository
     * @param UserBookmarkRepository $userBookmarkRepository
     * @param UserConversationRepository $userConversationRepository
     * @param UserBotProviderRepository $userBotProviderRepository
     * @param FreelanceProjectRepository $freelanceProjectRepository
     * @param UserSettingsRepository $userSettingsRepository
     */
    public function __construct(
        UserRepository $userRepository,
        UserCodeRepository $userCodeRepository,
        UserBookmarkRepository $userBookmarkRepository,
        UserConversationRepository $userConversationRepository,
        UserBotProviderRepository $userBotProviderRepository,
        FreelanceProjectRepository $freelanceProjectRepository,
        UserSettingsRepository $userSettingsRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userCodeRepository = $userCodeRepository;
        $this->userBookmarkRepository = $userBookmarkRepository;
        $this->userConversationRepository = $userConversationRepository;
        $this->userBotProviderRepository = $userBotProviderRepository;
        $this->freelanceProjectRepository = $freelanceProjectRepository;
        $this->userSettingsRepository = $userSettingsRepository;
    }

    /**
     * @param UserBotConversation $conversation
     * @param $settingsId
     * @return bool|string
     */
    public function updateUserSettings(UserBotConversation $conversation, $settingsId)
    {
        $user = $conversation->user;
        try {
            $this->guardIsUserSettings($user->id, $settingsId);
            $user->settings_id = $settingsId;
            $this->userRepository->save($user);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param UserBotConversation $conversation
     * @param integer $lastProjectId
     */
    public function updateConversationSettings($conversation, $lastProjectId)
    {
        $userBotProvider = $conversation->provider;
        $this->userBotProviderRepository->touch($userBotProvider, 'updated_at');

        $userBotProvider->last_project_id = $lastProjectId;
        $this->userBotProviderRepository->save($userBotProvider);
    }

    /**
     * @param UserBotConversation $conversation
     * @return bool
     */
    public function bindBotProviderToConversation(UserBotConversation $conversation)
    {
        $userBotProvider = $this->userBotProviderRepository
            ->findByUserIdAndChannelId(
                $conversation->user_id,
                $conversation->data['channelId']
            );
        if (null === $userBotProvider || UserBotProvider::STATUS_DISABLED === $userBotProvider->status) {
            return false;
        }
        $conversation->user_bot_provider_id = $userBotProvider->id;
        $this->userConversationRepository->save($conversation);

        $userBotProvider->last_project_id = $this->freelanceProjectRepository->findLastId();
        $this->userBotProviderRepository->save($userBotProvider);

        $this->userBotProviderRepository->touch($userBotProvider, 'updated_at');

        return true;
    }

    /**
     * @param UserBotConversation $conversation
     * @return bool
     */
    public function unbindBotProviderFromConversation(UserBotConversation $conversation)
    {
        $userBotProvider = $conversation->provider;
        if (null === $userBotProvider) {
            return false;
        }
        $conversation->user_bot_provider_id = null;
        $this->userConversationRepository->save($conversation);
        $this->userBotProviderRepository->touch($userBotProvider, 'updated_at');

        return true;
    }

    /**
     * @param UserBotConversation $conversation
     * @param $status
     */
    public function updateStatus($conversation, $status)
    {
        $conversation->status = $status;
        $this->userConversationRepository->save($conversation);
    }

    /**
     * @param UserBotConversation $conversation
     */
    public function unbindUser($conversation)
    {
        $conversation->user_id = null;
        $conversation->user_bot_provider_id = null;
        $conversation->status = UserBotConversation::STATUS_NOT_ACTIVE;
        $this->userConversationRepository->save($conversation);
    }

    /**
     * @param UserBotConversation $conversation
     * @param string $locale
     */
    public function changeLocale($conversation, $locale)
    {
        $conversation->locale = $locale;
        $this->userConversationRepository->save($conversation);
    }

    /**
     * @param UserBotConversation $conversation
     * @param string $code
     * @return bool
     */
    public function bindUser($conversation, $code)
    {
        $this->userCodeRepository->removeExpiredCodes();
        $userCode = $this->userCodeRepository->findByCode($code);
        if (null === $userCode) {
            return false;
        }
        $conversation->user_id = $userCode->user_id;
        $conversation->status = UserBotConversation::STATUS_ACTIVE;
        $this->userConversationRepository->save($conversation);

        $this->userCodeRepository->remove($userCode);

        return true;
    }

    /**
     * @param UserBotConversation $conversation
     * @param $identity
     * @return int
     */
    public function createCode($conversation, $identity)
    {
        $user = $this->userRepository->findByUsernameOrEmail($identity);
        if (null === $user) {
            return false;
        }
        $userCode = new UserCode();
        $userCode->user_id = $user->getId();
        $this->userCodeRepository->add($userCode);

        $conversation->user_id = $user->id;
        $this->userConversationRepository->save($conversation);

        return true;
    }

    /**
     * @param UserBotConversation $conversation
     * @return int
     */
    public function getCode($conversation)
    {
        $userCode = $this->userCodeRepository->findByUserId($conversation->user_id);
        if (null === $userCode) {
            $userCode = new UserCode();
            $userCode->user_id = $conversation->user_id;
            $this->userCodeRepository->add($userCode);
        }

        return true;
    }

    /**
     * @param string $conversationId
     * @param string $recipientId
     * @param array|object $token
     * @param array|object $data
     * @return UserBotConversation
     */
    public function create($conversationId, $recipientId, $token, $data)
    {
        $conversation = new UserBotConversation();
        $conversation->setAttributes([
            'conversation_id' => $conversationId,
            'recipient_id' => $recipientId,
            'token' => (array) $token,
            'data' => (array) $data,
            'status' => UserBotConversation::STATUS_NOT_ACTIVE,
        ]);
        $this->userConversationRepository->add($conversation);

        return $conversation;
    }

    /**
     * @param UserBotConversation $conversation
     * @param string $conversationId
     * @param string $recipientId
     * @param array|object $token
     * @param array|object $data
     * @return UserBotConversation
     */
    public function update($conversation, $conversationId, $recipientId, $token, $data)
    {
        $conversation->setAttributes([
            'conversation_id' => $conversationId,
            'recipient_id' => $recipientId,
            'token' => (array) $token,
            'data' => (array) $data,
        ]);
        $this->userConversationRepository->save($conversation);

        return $conversation;
    }

    /**
     * @param UserBotConversation $conversation
     * @param array|object $token
     * @return array|object
     */
    public function updateToken($conversation, $token)
    {
        $conversation->token = (array) $token;
        $this->userConversationRepository->save($conversation);

        return $conversation->token;
    }

    /**
     * @param integer $userId
     * @param integer $projectId
     * @return bool
     */
    public function addBookmark($userId, $projectId)
    {
        $bookmark = $this->userBookmarkRepository->findByUserIdAndProjectId($userId, $projectId);
        if (null !== $bookmark) {
            return false;
        }
        $bookmark = new UserBookmark();
        $bookmark->user_id = $userId;
        $bookmark->project_id = $projectId;
        $this->userBookmarkRepository->add($bookmark);

        return true;
    }

    /**
     * @param integer $userId
     * @param integer $userSettingsId
     * @throws \DomainException
     */
    public function guardIsUserSettings($userId, $userSettingsId)
    {
        if (false === $this->userSettingsRepository->existsByIdAndUserSettings($userSettingsId, $userId)) {
            throw new \DomainException(Yii::t('bot', 'It\'s not your settings list!'));
        }
    }
}
