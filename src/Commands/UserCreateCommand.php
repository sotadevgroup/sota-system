<?php

namespace Sota\System\Commands;

use InvalidArgumentException;
use Illuminate\Console\Command;
use App\Models\User;

class UserCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new User';


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
        $user = new User;
        $user->first_name = $this->ask('First name');
        $user->last_name = $this->ask('Last name');
        $user->email = $this->ask('Email Address');
        $user->password = $this->secret('password');
        $user->save();

        return $this->info("DONE. The user was created with id {$user->id}");
    }
}