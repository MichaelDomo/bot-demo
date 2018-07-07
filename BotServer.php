<?php

namespace app\components\bot;

use Yii;
use yii\base\Component;
use app\forms\ProjectSearchForm;
use app\models\FreelanceProjects;
use app\models\user\bot\UserBotConversation;
use app\components\bot\services\ConversationService;
use app\components\bot\repositories\FreelanceProjectRepository;
use app\components\bot\repositories\UserConversationRepository;

/**
 * Try to implement something like "server bot" that send message without user messages.
 */
class BotServer extends Component
{
    public $client;
    public $secret;

    private $conversations;
    private $conversationService;
    private $userConversationRepository;
    private $freelanceProjectRepository;

    /**
     * BotServer constructor.
     * @param UserConversationRepository $userConversationRepository
     * @param ConversationService $conversationService
     * @param FreelanceProjectRepository $freelanceProjectRepository
     * @param array $config
     */
    public function __construct(
        UserConversationRepository $userConversationRepository,
        ConversationService $conversationService,
        FreelanceProjectRepository $freelanceProjectRepository,
        $config = []
    ) {
        $this->userConversationRepository = $userConversationRepository;
        $this->conversationService = $conversationService;
        $this->freelanceProjectRepository = $freelanceProjectRepository;
        parent::__construct($config);
    }

    /**
     * Init requests
     */
    public function initRequests()
    {
        if (null !== ($conversations = $this->getConversations())) {
            $lastId = $this->freelanceProjectRepository->findLastId();
            foreach ($conversations as $conversation) {
                $form = $this->buildProjectSearchForm(
                    $conversation->user->currentSettings,
                    $conversation->provider->last_project_id,
                    $lastId
                );

                $records = $form->build(Yii::createObject(FreelanceProjects::class));
                if (count($records) > 0) {
                    $bot = BotFactory::buildChanel(
                        $this->client,
                        $this->secret,
                        $conversation->provider->bot->channel_id
                    );
                    $bot->setConversation($conversation);
                    $bot->notifyEventHandler($records);
                }

                $this->conversationService->updateConversationSettings($conversation, $lastId);
                unset($bot, $form, $records);
            }
        }
    }

    /**
     * @param \app\models\user\UserSettings $userSettings
     * @param integer $fromId
     * @param integer $toId
     * @return ProjectSearchForm
     */
    private function buildProjectSearchForm($userSettings, $fromId, $toId)
    {
        $form = new ProjectSearchForm();
        $form->setSettings($userSettings);
        $form->searchAfter = 1;
        $form->id = $fromId;
        $form->lastId = $toId;

        return $form;
    }

    /**
     * @return UserBotConversation[]
     */
    private function getConversations()
    {
        if (null === $this->conversations) {
            $this->conversations = $this->userConversationRepository->findAllActive();
        }

        return $this->conversations;
    }
}
