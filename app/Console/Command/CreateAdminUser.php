<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin {--role=admin : The role of the user (admin or superadmin)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin or superadmin user';

    /**
     * The user repository instance.
     *
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * Create a new command instance.
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = $this->option('role');
        
        if (!in_array($role, ['admin', 'superadmin'])) {
            $this->error('Invalid role. Role must be either "admin" or "superadmin".');
            return 1;
        }
        
        $this->info("Creating new $role user...");
        
        $name = $this->ask('Name');
        $username = $this->ask('Username');
        $email = $this->ask('Email');
        $password = $this->secret('Password');
        $passwordConfirmation = $this->secret('Confirm Password');
        
        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }
        
        // Create user
        $user = $this->userRepository->create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'email_verified_at' => now(),
        ]);
        
        $this->info("$role user created successfully!");
        $this->table(
            ['ID', 'Name', 'Username', 'Email', 'Role'],
            [[$user->id, $user->name, $user->username, $user->email, $user->role]]
        );
        
        return 0;
    }
}