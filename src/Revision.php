<?php

namespace Pdffiller\RevisionableMultiauth;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Venturecraft\Revisionable\Revision as VenturecraftRevision;

/**
 * Revision.
 *
 * Base model to allow for revision history on
 * any model that extends this model
 *
 * (c) Venture Craft <http://www.venturecraft.com.au>
 */
class Revision extends VenturecraftRevision
{
    /**
     * User Responsible.
     *
     * @return User|bool user responsible for the change
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function userResponsible()
    {
        if (empty($this->user_id)) {
            return false;
        }
        if (class_exists($class = '\Cartalyst\Sentry\Facades\Laravel\Sentry')
            || class_exists($class = '\Cartalyst\Sentinel\Laravel\Facades\Sentinel')
        ) {
            return $class::findUserById($this->user_id);
        } else {
            $userModel = app('config')->get('auth.model');

            if (empty($userModel)) {
                $guards = array_keys(config('auth.guards'));

                foreach ($guards as $guard) {
                    $provider = config("auth.guards.{$guard}.provider");
                    $userModel = config("auth.providers.{$provider}.model");

                    if (!empty($userModel)) {
                        break;
                    }
                }

                if (empty($userModel)) {
                    return false;
                }
            }
            if (!class_exists($userModel)) {
                return false;
            }

            return $userModel::find($this->user_id);
        }
    }
}
