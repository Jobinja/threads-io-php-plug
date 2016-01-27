<?php

namespace Jobinja\ThreadsIo\Interfaces;

/**
 * Implements the ability for user entity to be tracked by Threads.io
 * @see https://docs.threads.io/docs/record-a-page-view
 *
 * Interface EventThreadableInterface
 * @package Jobinja\ThreadsIo\Interfaces
 */
interface PageThreadableInterface extends AbstractThreadableInterface {

    /**
     * This getter returns a string to be used in Threads.io as the title of a visited Page.
     * @return string
     */
    public function getThreadsIoTitle();

    /**
     * This getter returns an array containing the information to be logged in Threads.io
     * @return array
     */
    public function getThreadsIoProperties();

    /**
     * This getter returns the DateTimeImmutable of when the Page has been visited.
     * @return \DateTimeImmutable
     */
    public function getThreadsIoDateTime();
}