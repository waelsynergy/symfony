<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class FileUploadService
{
    public function __construct(private string $projectDir)
    {
    }

    private function upload(UploadedFile $file, string $destinationDirectory, ?string $oldFilename = null): ?string
    {
        $this->delete($destinationDirectory, $oldFilename);
        $filename = Uuid::v4() . '.' . $file->guessExtension();
        $file->move($destinationDirectory, $filename);
        return $filename;
    }

    private function delete(string $destinationDirectory, ?string $oldFilename)
    {
        if ($oldFilename) {
            $currentFilename = $destinationDirectory . '/' . $oldFilename;
            if (file_exists($currentFilename)) unlink($currentFilename);
        }
    }

    public function uploadProjectImage(Project $project, UploadedFile $file)
    {
        $project->setImage($this->upload($file, $this->projectDir . '/public/projects', $project->getImage()));
    }

    public function removeProjectImage(string $image)
    {
        $this->delete($this->projectDir .  '/public/projects', $image);
    }
}
