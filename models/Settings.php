<?php

namespace mikp\sanctum\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'sanctum-backend-menu';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    // ajax run console command
    public function onRunAuthorizationConsoleCommand()
    {
        $this->call('mikp:authorization', [
            '--add' => true,
            '-y' => true
        ]);
    }
}
