<?php

namespace FBI\UserBundle\Command;


use FBI\UserBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GrantUserRoleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:grant')
            ->setDescription('Grants role to user')
            ->setHelp("This command allows you to grant roles to users")
            ->addArgument('username')
            ->addArgument('role')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $userRepo = $doctrine->getRepository('FBIUserBundle:User');
        $roleRepo = $doctrine->getRepository('FBIUserBundle:Role');

        $role = $roleRepo->findOneBy(['role' => $input->getArgument('role')]);

        if (!$role) {
            $role = Role::create($input->getArgument('role'));
            $output->writeln(sprintf("Creating new role %s", $role->getRole()));
        }

        if ($user = $userRepo->findOneBy(['username' => $username])) {
            if ($user->hasRole($role)) {
                $output->writeln(sprintf("User already has role %s", $role->getRole()));
            }
            else {
                $user->addRole($role);
                $em->persist($user);
                $em->flush();
                $output->writeln(sprintf("Added %s to user %s", $role->getRole(), $user->getUsername()));
            }
        }
        else {
            $output->writeln('User does not exist');
        }
    }
}
