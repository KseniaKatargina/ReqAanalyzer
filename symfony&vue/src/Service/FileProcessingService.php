<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use Imagick;
use thiagoalessio\TesseractOCR\TesseractOCR;

class FileProcessingService
{
    public function convertFileToText(UploadedFile $file): ?string
    {
        $mimeType = $file->getMimeType();

        if ($mimeType === 'text/plain') {
            return file_get_contents($file->getPathname());
        } elseif ($mimeType === 'application/pdf') {
            return $this->extractTextFromPdf($file);
        } elseif ($mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
            $mimeType === 'application/msword') {
            return $this->extractTextFromDocx($file);
        }

        return null;
    }
    private function extractTextFromPdf(UploadedFile $file): ?string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = (string)$pdf->getText();

            // Убираем служебный текст от TCPDF
            $text = preg_replace('/\bPowered by TCPDF\b.*$/s', '', $text);

            $image_text = $this->extractTextFromImages($file->getPathname());
            error_log($image_text);
            return trim($text . "\n" . $image_text);
        } catch (\Exception $e) {
            return "Ошибка: " . $e->getMessage();
        }
    }

    function extractTextFromImages($filePath): string
    {
        $imagePaths = $this->convertPdfToImages($filePath);
        $extractedText = '';

        foreach ($imagePaths as $image) {
            $extractedText .= (new TesseractOCR($image))->lang('rus')->run() . "\n";
            unlink($image); // Удаляем временные файлы изображений
        }

        return $extractedText;
    }

    function convertPdfToImages($filePath): array
    {
        $imagick = new Imagick();
        $imagick->setResolution(300, 300); // Разрешение для лучшего OCR
        $imagick->readImage($filePath);
        $imagePaths = [];

        foreach ($imagick as $i => $image) {
            $imagePath = sys_get_temp_dir() . "/page_{$i}.png";
            $image->setImageFormat('png');
            $image->writeImage($imagePath);
            $imagePaths[] = $imagePath;
        }

        return $imagePaths;
    }
    private function extractTextFromDocx(UploadedFile $file): ?string
    {
        try {
            $phpWord = IOFactory::load($file->getPathname());
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }

            // Проверяем изображения и извлекаем текст
            $imageText = $this->extractTextFromImagesInDocx($file->getPathname());
            error_log($imageText);
            return trim($text . "\n" . $imageText);
        } catch (\Exception $e) {
            return "Ошибка: " . $e->getMessage();
        }
    }
    private function extractTextFromImagesInDocx($filePath): string
    {
        // Извлекаем изображения из DOCX
        $imagePaths = $this->extractImagesFromDocx($filePath);
        $extractedText = '';

        foreach ($imagePaths as $image) {
            // Извлекаем текст с изображений через OCR
            $extractedText .= (new TesseractOCR($image))->lang('rus')->run() . "\n";
            unlink($image); // Удаляем временные файлы изображений
        }

        return $extractedText;
    }

    private function extractImagesFromDocx($filePath): array
    {
        $imagePaths = [];
        // Загружаем DOCX файл
        $zip = new \ZipArchive();
        $zip->open($filePath);

        // Получаем изображения внутри DOCX
        $imageDir = 'word/media/';  // Папка с изображениями в DOCX
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_contains($filename, $imageDir)) {
                $imageContent = $zip->getFromIndex($i);
                $imagePath = sys_get_temp_dir() . '/' . basename($filename);
                file_put_contents($imagePath, $imageContent);
                $imagePaths[] = $imagePath;
            }
        }
        $zip->close();

        return $imagePaths;
    }
}
