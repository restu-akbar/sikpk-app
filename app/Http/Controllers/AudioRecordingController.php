<?php

namespace App\Http\Controllers;

use App\Models\AudioRecording;

class AudioRecordingController extends Controller
{
    public function show(AudioRecording $audioRecording)
    {
        $path = storage_path('app/private/' . $audioRecording->path);

        return response()->file($path, [
            'Content-Type' => $audioRecording->mime_type ?? 'audio/webm',
        ]);
    }
}
