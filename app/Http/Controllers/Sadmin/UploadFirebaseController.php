<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\File;
use App\Models\Presence;
use App\Models\StudentLog;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class UploadFirebaseController extends Controller
{
    public $bucket = 'unt-dev.firebasestorage.app';
    public $base_path = 'KardiologiFkkmk';
    protected $firebaseStorage;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDefaultStorageBucket($this->bucket);

        $this->firebaseStorage = $factory->createStorage();
    }

    public function firebaseUpload(Request $request)
    {
        switch ($request->model) {
            case 'document':
                return $this->uploadDocument();
            case 'file':
                return $this->uploadFile();
            case 'checkin':
                return $this->uploadPresenceCheckin();
            case 'student_log':
                return $this->uploadStudentLog();
            default:
                return "no model";
        }
    }

    public function uploadDocument()
    {
        $documents = Document::where('file', 'like', 'documents%')
            ->limit(10)
            ->get();

        $failed = [];
        $success = [];
        foreach ($documents as $document) {
            if (strpos($document['file'], 'https://') === false) {
                $file = public_path('storage/' . $document['file']);
                if (!file_exists($file)) {
                    $failed[] = $document['id'];
                } else {
                    $bucket = $this->firebaseStorage->getBucket();
                    try {
                        $object = $bucket->upload(
                            fopen($file, 'r'),
                            ['name' => $this->base_path . '/' . $document['file']]
                        );

                        $new_firebase_path = 'https://firebasestorage.googleapis.com/v0/b/' .
                            $bucket->name() . '/o/' .
                            str_replace("@", "%40",
                                str_replace('/', '%2F', $object->name())) .
                            '?alt=media';

                        $document->update([
                            'file' => $new_firebase_path,
                        ]);

                        unlink($file);

                        $success[] = $document['id'];
                    } catch (\Exception $e) {
                        $failed[] = $document['id'];
                    }
                }
            }
        }

        return [
            'success' => $success,
            'failed'  => $failed,
        ];
    }

    public function uploadStudentLog()
    {
        $student_logs = StudentLog::where('photo', 'like', 'logbook%')
            ->limit(10)
            ->get();

        $failed = [];
        $success = [];
        foreach ($student_logs as $student_log) {
            if (strpos($student_log['photo'], 'https://') === false) {
                $file = public_path('storage/' . $student_log['photo']);
                if (!file_exists($file)) {
                    $failed[] = $student_log['id'];
                } else {
                    $bucket = $this->firebaseStorage->getBucket();
                    try {
                        $object = $bucket->upload(
                            fopen($file, 'r'),
                            ['name' => $this->base_path . '/StudentLog/' .
                                date("Ym", strtotime($student_log->created_at)) .'/' . $student_log['photo']]
                        );

                        $new_firebase_path = 'https://firebasestorage.googleapis.com/v0/b/' .
                            $bucket->name() . '/o/' .
                            str_replace("@", "%40",
                                str_replace('/', '%2F', $object->name())) .
                            '?alt=media';

                        $student_log->update([
                            'photo' => $new_firebase_path,
                        ]);

                        unlink($file);

                        $success[] = $student_log['id'];
                    } catch (\Exception $e) {
                        $failed[] = $student_log['id'];
                    }
                }
            }
        }

        return [
            'success' => $success,
            'failed'  => $failed,
        ];
    }

    public function uploadPresenceCheckin()
    {
        $presences = Presence::where('checkin_photo', 'like', '/daily%')
            ->whereDate('checkin', '>', '2024-04-01')
            ->limit(10)
            ->get();

        $failed = [];
        $success = [];
        foreach ($presences as $presence) {
            if (strpos($presence['checkin_photo'], 'https://') === false) {
                $file = public_path('storage' . $presence['checkin_photo']);
                if (!file_exists($file)) {
                    $failed[] = $presence['id'];
                } else {
                    $bucket = $this->firebaseStorage->getBucket();
                    try {
                        $object = $bucket->upload(
                            fopen($file, 'r'),
                            ['name' => $this->base_path . $presence['checkin_photo']]
                        );

                        $new_firebase_path = 'https://firebasestorage.googleapis.com/v0/b/' .
                            $bucket->name() . '/o/' .
                            str_replace("@", "%40",
                                str_replace('/', '%2F', $object->name())) .
                            '?alt=media';

                        $presence->update([
                            'checkin_photo' => $new_firebase_path,
                        ]);

                        unlink($file);

                        $success[] = $presence['id'];
                    } catch (\Exception $e) {
                        $failed[] = $presence['id'];
                    }
                }
            }
        }

        return [
            'success' => $success,
            'failed'  => $failed,
        ];
    }

    public function uploadFile()
    {
        $tasks = File::where('link', 'like', 'task%')
            ->limit(10)
            ->get();

        $failed = [];
        $success = [];
        foreach ($tasks as $task) {
            if (strpos($task['link'], 'https://') === false) {
                $file = public_path('storage/' . $task['link']);
                if (!file_exists($file)) {
                    $failed[] = $task['id'];
                } else {
                    $bucket = $this->firebaseStorage->getBucket();
                    try {
                        $object = $bucket->upload(
                            fopen($file, 'r'),
                            ['name' => $this->base_path . '/Files/' .
                                date("Ym", strtotime($task->created_at)) .'/' . $task['link']]
                        );

                        $new_firebase_path = 'https://firebasestorage.googleapis.com/v0/b/' .
                            $bucket->name() . '/o/' .
                            str_replace("@", "%40",
                                str_replace('/', '%2F', $object->name())) .
                            '?alt=media';

                        $task->update([
                            'link' => $new_firebase_path,
                        ]);

                        unlink($file);

                        $success[] = $task['id'];
                    } catch (\Exception $e) {
                        $failed[] = $task['id'];
                    }
                }
            }
        }

        return [
            'success' => $success,
            'failed'  => $failed,
        ];
    }

    public function getDir(Request $request)
    {
        $bucket = $this->firebaseStorage->getBucket();

        $objects = $bucket->objects([
            'prefix' => "KaryawanLog/" . $request->karyawan_id . "/",
            // 'delimiter' => '/',
        ]);

        $array = [];

        foreach ($objects as $object) {
            $fileName = $object->name();

            // Check if the file has a .log extension
            if (pathinfo($fileName, PATHINFO_EXTENSION) === 'log') {

                // Get the file content
                $stream = $object->downloadAsStream();
                $content = $stream->getContents();

                // Output the content
                $array[] = json_decode($content);
            }
        }

        return collect($array);
    }
}
