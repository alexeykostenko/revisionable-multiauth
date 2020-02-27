<?php


namespace Pdffiller\RevisionableMultiauth;

use Illuminate\Support\Facades\Auth;

trait Userable
{
    public function getCurrentAuthGuard()
    {
        try {
            $guards = array_keys(config('auth.guards'));

            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    return $guard;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function getSystemUserId()
    {
        try {
            if ($systemUser = $this->getSystemUser()) {
                return $systemUser->getAuthIdentifier();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    public function getSystemUser()
    {
        try {
            if ($guard = $this->getCurrentAuthGuard()) {
                return Auth::guard($guard)->user();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    protected function getParentClass()
    {
        return (new \ReflectionClass($this))->getParentClass()->getName();
    }

    public function getMorphClass()
    {
        return $this->getParentClass();
    }
}
