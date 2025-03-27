<?php

namespace App\Service;

use App\Repository\TextAnalysesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NlpService
{
    private $textAnalysesRepository;

    /**
     * @param $textAnalysesRepository
     */
    public function __construct(TextAnalysesRepository $textAnalysesRepository)
    {
        $this->textAnalysesRepository = $textAnalysesRepository;
    }


    public function sendToNLPService(array $text, string $token): array
    {
        $nlpServiceUrl = 'http://localhost:5000/analyze';

        $ch = curl_init($nlpServiceUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($text));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false || $httpCode !== 202) {
            return ['error' => 'Ошибка запроса к NLP сервису'];
        }

        $task = json_decode($result, true);
        if (!isset($task['task_id'])) {
            return ['error' => 'Ответ NLP сервиса не содержит task_id'];
        }

        return ['task_id' => $task['task_id']];
    }

//    public function sendToNLPService(array $text, string $token): array
//    {
//        set_time_limit(300);
//        $nlpServiceUrl = 'http://localhost:5000/analyze';
//
//        $ch = curl_init($nlpServiceUrl);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($text));
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [
//            "Content-Type: application/json",
//            "Authorization: Bearer $token"
//        ]);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//        $result = curl_exec($ch);
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        curl_close($ch);
//
//
//        if ($result === false || $httpCode !== 202) {
//            return ['error' => 'Ошибка запроса к NLP сервису'];
//        }
//
//        $task = json_decode($result, true);
//        if (!isset($task['task_id'])) {
//            return ['error' => 'Ответ NLP сервиса не содержит task_id'];
//        }
//
//        $taskId = $task['task_id'];
//
//        $statusUrl = "http://localhost:5000/tasks/$taskId";
//
//        while (true) {
//            sleep(2);
//            $status = file_get_contents($statusUrl);
//            if ($status === false) {
//                return ['error' => 'Ошибка получения статуса задачи'];
//            }
//
//            $statusData = json_decode($status, true);
//            if (!isset($statusData['status'])) {
//                return ['error' => 'Некорректный ответ от NLP сервиса'];
//            }
//
//            if ($statusData['status'] === 'SUCCESS') {
//                return $statusData;
//            } elseif ($statusData['status'] !== 'PENDING') {
//                return ['error' => 'Ошибка выполнения задачи'];
//            }
//        }
//    }
}
