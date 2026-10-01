<?php

namespace App\Console\Commands;

use App\Enums\SystemRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateSuperAdmin extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:create-super-admin
                            {--name= : The name of the user}
                            {--email= : The email address of the user}
                            {--password= : The password (prompted for when omitted)}';

    /**
     * @var string
     */
    protected $description = 'Create a Super Admin user, or promote an existing user to Super Admin';

    public function handle(): int
    {
        // Make sure the roles and permissions exist - the seeder is safe to run repeatedly.
        $this->callSilently('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);

        $email = $this->option('email') ?: text(
            label: 'Email address',
            required: true,
            validate: ['email' => 'email'],
        );

        if ($user = User::where('email', $email)->first()) {
            return $this->promote($user);
        }

        $data = [
            'name' => $this->option('name') ?: text(label: 'Name', required: true),
            'email' => $email,
            'password' => $this->option('password') ?: password(
                label: 'Password',
                required: true,
                validate: ['password' => Password::defaults()],
            ),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([...$validator->validated(), 'locale' => config('app.locale')]);
        $user->markEmailAsVerified();
        $user->assignRole(SystemRole::SuperAdmin->role());

        $this->components->info("Super Admin [{$user->email}] created. Log in at ".route('login'));

        return self::SUCCESS;
    }

    private function promote(User $user): int
    {
        if ($user->isSuperAdmin()) {
            $this->components->info("[{$user->email}] is already a Super Admin.");

            return self::SUCCESS;
        }

        if (! confirm("A user with the email [{$user->email}] already exists. Make them a Super Admin?", default: true)) {
            return self::FAILURE;
        }

        $user->assignRole(SystemRole::SuperAdmin->role());

        $this->components->info("[{$user->email}] is now a Super Admin.");

        return self::SUCCESS;
    }
}
