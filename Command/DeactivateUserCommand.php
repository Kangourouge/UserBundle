<?php

namespace KRG\UserBundle\Command;

use KRG\UserBundle\Util\UserManipulator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DeactivateUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('krg:user:deactivate')
            ->setDescription('Deactivate a user')
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            ])
            ->setHelp(<<<'EOT'
The <info>krg:user:deactivate</info> command deactivates a user (will not be able to log in)

  <info>php %command.full_name% matthieu</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        $manipulator = $this->getContainer()->get(UserManipulator::class);
        $manipulator->deactivate($username);

        $output->writeln(sprintf('User "%s" has been deactivated.', $username));
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $question = new Question('Please choose a username:');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $answer = $this->getHelper('question')->ask($input, $output, $question);

            $input->setArgument('username', $answer);
        }
    }
}
