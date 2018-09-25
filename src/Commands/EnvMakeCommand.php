<?php

namespace Sota\System\Commands;

use InvalidArgumentException;
use Illuminate\Console\Command;

class EnvMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:make {--force}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make the .env file';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (file_exists(app()->environmentFilePath()) && !$this->hasOption('force')) {
          return $this->error("The .env file already exists");
        }
        
        $contents = file_get_contents(__DIR__.'/../../resources/env/.env.example');

        // APP

        $APP_NAME_DEFAULT = basename(base_path());
        $APP_NAME = $this->anticipate('APP_NAME', [ $APP_NAME_DEFAULT ], $APP_NAME_DEFAULT);

        $APP_ENV = $this->choice('APP_ENV', ['local', 'preview', 'staging', 'production'], 0);

        $APP_DEBUG_DEFAULT = $APP_ENV === 'local' || $APP_ENV === 'preview' ? 1 : 0;
        $APP_DEBUG = $this->choice('APP_DEBUG', ['false', 'true'], $APP_DEBUG_DEFAULT);

        $APP_URL_DEFAULT = $APP_ENV === 'local' ? 'http://localhost:8000' : null;
        $APP_URL = $this->ask('APP_URL', $APP_URL_DEFAULT);

        $contents = str_replace("APP_NAME=", "APP_NAME={$APP_NAME}", $contents);
        $contents = str_replace("APP_ENV=", "APP_ENV={$APP_ENV}", $contents);
        $contents = str_replace("APP_DEBUG=", "APP_DEBUG={$APP_DEBUG}", $contents);
        $contents = str_replace("APP_URL=", "APP_URL={$APP_URL}", $contents);


        // DB

        $DB_CONNECTION_DEFAULT = 'mysql';
        $DB_CONNECTION = $this->anticipate('DB_CONNECTION', [ $DB_CONNECTION_DEFAULT ], $DB_CONNECTION_DEFAULT);

        $DB_HOST_DEFAULT = '127.0.0.1';
        $DB_HOST = $this->anticipate('DB_HOST', [ $DB_HOST_DEFAULT ], $DB_HOST_DEFAULT);

        $DB_PORT_DEFAULT = '3306';
        $DB_PORT = $this->anticipate('DB_PORT', [ $DB_PORT_DEFAULT ], $DB_PORT_DEFAULT);

        $DB_DATABASE_DEFAULT = strtolower(str_replace('-','',$APP_NAME));
        $DB_DATABASE = $this->anticipate('DB_DATABASE', [ $DB_DATABASE_DEFAULT ], $DB_DATABASE_DEFAULT);

        $DB_USERNAME_DEFAULT = $APP_ENV === 'local' ? 'root' : $DB_DATABASE;
        $DB_USERNAME = $this->anticipate('DB_USERNAME', [ 'root' , $DB_DATABASE ], $DB_USERNAME_DEFAULT);

        $DB_PASSWORD = $this->ask('DB_PASSWORD');

        $contents = str_replace("DB_CONNECTION=", "DB_CONNECTION={$DB_CONNECTION}", $contents);
        $contents = str_replace("DB_HOST=", "DB_HOST={$DB_HOST}", $contents);
        $contents = str_replace("DB_PORT=", "DB_PORT={$DB_PORT}", $contents);
        $contents = str_replace("DB_DATABASE=", "DB_DATABASE={$DB_DATABASE}", $contents);
        $contents = str_replace("DB_USERNAME=", "DB_USERNAME={$DB_USERNAME}", $contents);
        $contents = str_replace("DB_PASSWORD=", "DB_PASSWORD={$DB_PASSWORD}", $contents);


        // SENTRY

        if ($APP_ENV !== 'local') {

          $SENTRY_DSN = $this->ask('SENTRY_DSN');
          
          $contents = str_replace("SENTRY_DSN=", "SENTRY_DSN={$SENTRY_DSN}", $contents);

        }
        

        // BACKUPS

        if ($APP_ENV !== 'local') {

          $BACKUPS_AWS_KEY = $this->ask('BACKUPS_AWS_KEY');
          $BACKUPS_AWS_SECRET = $this->ask('BACKUPS_AWS_SECRET');
          $BACKUPS_AWS_REGION = $this->ask('BACKUPS_AWS_REGION', 'eu-west-2');
          $BACKUPS_AWS_BUCKET = $this->ask('BACKUPS_AWS_BUCKET');
          $BACKUPS_SLACK_WEBOOK = $this->ask('BACKUPS_SLACK_WEBOOK');
          $BACKUPS_EMAIL = $this->ask('BACKUPS_EMAIL');

          $contents = str_replace("BACKUPS_AWS_KEY=", "BACKUPS_AWS_KEY={$BACKUPS_AWS_KEY}", $contents);
          $contents = str_replace("BACKUPS_AWS_SECRET=", "BACKUPS_AWS_SECRET={$BACKUPS_AWS_SECRET}", $contents);
          $contents = str_replace("BACKUPS_AWS_REGION=", "BACKUPS_AWS_REGION={$BACKUPS_AWS_REGION}", $contents);
          $contents = str_replace("BACKUPS_AWS_BUCKET=", "BACKUPS_AWS_BUCKET={$BACKUPS_AWS_BUCKET}", $contents);
          $contents = str_replace("BACKUPS_SLACK_WEBOOK=", "BACKUPS_SLACK_WEBOOK={$BACKUPS_SLACK_WEBOOK}", $contents);
          $contents = str_replace("BACKUPS_EMAIL=", "BACKUPS_EMAIL={$BACKUPS_EMAIL}", $contents);
        
        }

        // Create File
        
        $envFilePath = app()->environmentFilePath();

        $this->writeFile($envFilePath, $contents);

        // Generate App Key

        $this->call('key:generate');
        $this->line('');
        $this->call('jwt:secret');
        $this->line('');

        return $this->info('DONE. The env file was created successfully');
    }


    /**
     * Overwrite the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @return boolean
     */
    protected function writeFile(string $path, string $contents): bool
    {
        $file = fopen($path, 'w');
        fwrite($file, $contents);
        return fclose($file);
    }
}