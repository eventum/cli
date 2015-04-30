<?php

namespace Eventum\Console;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * The Input/Output helper.
 */
class IO
{
    protected $input;
    protected $output;
    protected $helperSet;

    /**
     * Constructor.
     *
     * @param InputInterface $input The input instance
     * @param OutputInterface $output The output instance
     * @param HelperSet $helperSet The helperSet instance
     */
    public function __construct(InputInterface $input, OutputInterface $output, HelperSet $helperSet)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helperSet = $helperSet;
    }

    /**
     * {@inheritDoc}
     */
    public function ask($question, $default = null)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new Question($question, $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * {@inheritDoc}
     */
    public function askHidden($question, $default = null)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new Question($question, $default);
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * {@inheritDoc}
     */
    public function askChoices($question, $choices, $errorMessage, $default = null)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->helperSet->get('question');
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setErrorMessage($errorMessage);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * {@inheritDoc}
     */
    public function askConfirmation($question, $default = true)
    {
        return $this->helperSet->get('dialog')->askConfirmation($this->output, $question, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function askAndValidate($question, $validator, $attempts = false, $default = null)
    {
        return $this->helperSet->get('dialog')->askAndValidate(
            $this->output, $question, $validator, $attempts, $default
        );
    }

}
