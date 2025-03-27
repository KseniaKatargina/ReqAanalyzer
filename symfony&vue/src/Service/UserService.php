<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\PasswordUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function registerUser(string $email, string $password): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Некорректный email'];
        }

        if ($this->checkUser($email)) {
            return ['error' => 'Такой email уже зарегистрирован'];
        }

        if (!PasswordUtils::isPasswordValid($password)) {
            return ['error' => 'Пароль должен содержать не менее 5 символов, одну заглавную букву и одну цифру'];
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->save($user);

        return ['status' => 'User created'];
    }

    public function validateUser(string $email, string $password): bool
    {
        $user = $this->checkUser($email);
        return $user && $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function checkUser(string $email): ?User
    {
        return $this->userRepository->findOneByEmail($email);
    }

    public function checkPassword(User $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    public function updateUser(User $user, array $data): void
    {
        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            $existingUser = $this->checkUser($data['email']);
            if ($existingUser) {
                throw new \RuntimeException('Этот email уже зарегистрирован');
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['newPassword']) && isset($data['oldPassword'])) {
            if (!$this->checkPassword($user, $data['oldPassword'])) {
                throw new \RuntimeException('Неверный старый пароль');
            }

            if ($data['newPassword'] !== $data['confirmNewPassword']) {
                throw new \RuntimeException('Пароли не совпадают');
            }

            if (!preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $data['newPassword'])) {
                throw new \RuntimeException('Пароль должен содержать минимум 8 символов, 1 заглавную букву и 1 цифру');
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $data['newPassword']));
        }

        $this->userRepository->save($user);
    }
}