<?php


namespace App\Service;

use App\Repository\TextAnalysesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class FileService
{
    private TextAnalysesRepository $textAnalysesRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(TextAnalysesRepository $textAnalysesRepository, EntityManagerInterface $entityManager)
    {
        $this->textAnalysesRepository = $textAnalysesRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteFile(int $id, int $userId): void
    {
        $file = $this->textAnalysesRepository->find($id);
        if (!$file) {
            throw new \RuntimeException('Файл не найден', Response::HTTP_NOT_FOUND);
        }

        if ($file->getUserId()->getId() !== $userId) {
            throw new \RuntimeException('Этот файл не принадлежит текущему пользователю', Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($file);
        $this->entityManager->flush();
    }

    public function editFile(int $id, int $userId, array $data): void
    {
        $file = $this->textAnalysesRepository->find($id);
        if (!$file) {
            throw new \RuntimeException('Файл не найден', Response::HTTP_NOT_FOUND);
        }

        if ($file->getUserId()->getId() !== $userId) {
            throw new \RuntimeException('Этот файл не принадлежит текущему пользователю', Response::HTTP_FORBIDDEN);
        }

        if (isset($data['title'])) {
            $file->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $file->setDescription($data['description']);
        }

        $this->entityManager->flush();
    }
    public function getFile(int $id, int $userId): ?array
    {
        $file = $this->textAnalysesRepository->findOneBy(['id' => $id, 'userId' => $userId]);

        if (!$file) {
            return null;
        }

        return [
            'title' => $file->getTitle(),
            'original_text' => $file->getOriginalText(),
            'created_at' => $file->getCreatedAt(),
            'description' => $file->getDescription(),
        ];
    }
    public function generateFile(string $fileName, string $content, string $format): string
    {
        $directory = sys_get_temp_dir();
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName . '.' . $format; // Добавляем новое расширение

        try {
            switch ($format) {
                case 'txt':
                    file_put_contents($filePath, $content);
                    break;

                case 'pdf':
                    try {
                        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                        $pdf->AddPage();
                        $pdf->SetFont('dejavusans', '', 12);
                        $pdf->Write(0, $content);
                        $pdf->Output($filePath, 'F');
                    } catch (\Exception $e) {
                        throw new \Exception('Ошибка генерации PDF: ' . $e->getMessage());
                    }
                    break;

                case 'docx':
                    $phpWord = new \PhpOffice\PhpWord\PhpWord();
                    $phpWord->setDefaultFontName('Arial');
                    $phpWord->setDefaultFontSize(12);
                    $section = $phpWord->addSection();
                    $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                    $section->addText($content);
                    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                    $objWriter->save($filePath);
                    break;
            }

            if (!file_exists($filePath)) {
                throw new \Exception("Ошибка: файл $filePath не найден после генерации!");
            }

            return $filePath;
        } catch (\Exception $e) {
            throw new \Exception("Ошибка при создании файла: " . $e->getMessage());
        }
    }
}