<?php

namespace Pdffiller\RevisionableMultiauth;

use Illuminate\Database\Eloquent\Relations\Relation;
use Venturecraft\Revisionable\RevisionableTrait as VenturecraftRevisionableTrait;
use Illuminate\Support\Arr;

/*
 * This file is part of the Revisionable package by Venture Craft
 *
 * (c) Venture Craft <http://www.venturecraft.com.au>
 *
 */

/**
 * Class RevisionableTrait
 * @package Venturecraft\Revisionable
 */
trait RevisionableTrait
{
    use  VenturecraftRevisionableTrait;

    /**
     * Called after a model is successfully saved.
     *
     * @return void
     */
    public function postSave()
    {
        if (isset($this->historyLimit) && $this->revisionHistory()->count() >= $this->historyLimit) {
            $LimitReached = true;
        } else {
            $LimitReached = false;
        }

        if (isset($this->revisionCleanup)) {
            $RevisionCleanup=$this->revisionCleanup;
        } else {
            $RevisionCleanup=false;
        }

        // check if the model already exists
        if (((!isset($this->revisionEnabled) || $this->revisionEnabled) && $this->updating) && (!$LimitReached || $RevisionCleanup)) {
            // if it does, it means we're updating

            $changes_to_record = $this->changedRevisionableFields();

            $revisions = [];

            foreach ($changes_to_record as $key => $change) {
                $revisions[] = [
                    'revisionable_type' => $this->getMorphClass(),
                    'revisionable_id' => $this->getKey(),
                    'key' => $key,
                    'old_value' => $this->formatRevisionableValue(Arr::get($this->originalData, $key)),
                    'new_value' => $this->formatRevisionableValue($this->updatedData[$key]),
                    'user_type' => $this->getSystemUserType(),
                    'user_id' => $this->getSystemUserId(),
                    'created_at' => new \DateTime(),
                    'updated_at' => new \DateTime(),
                ];
            }

            if (count($revisions) > 0) {
                if($LimitReached && $RevisionCleanup){
                    $toDelete = $this->revisionHistory()->orderBy('id','asc')->limit(count($revisions))->get();
                    foreach($toDelete as $delete){
                        $delete->delete();
                    }
                }
                $revision = new Revision;
                \DB::table($revision->getTable())->insert($revisions);
                \Event::dispatch('revisionable.saved', ['model' => $this, 'revisions' => $revisions]);
            }
        }
    }

    /**
     * Called after record successfully created
     */
    public function postCreate()
    {
        // Check if we should store creations in our revision history
        // Set this value to true in your model if you want to
        if(empty($this->revisionCreationsEnabled)) {
            // We should not store creations.
            return false;
        }

        if ((!isset($this->revisionEnabled) || $this->revisionEnabled)) {
            $revisions[] = [
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id' => $this->getKey(),
                'key' => self::CREATED_AT,
                'old_value' => null,
                'new_value' => $this->{self::CREATED_AT},
                'user_type' => $this->getSystemUserType(),
                'user_id' => $this->getSystemUserId(),
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ];

            $revision = new Revision;
            \DB::table($revision->getTable())->insert($revisions);
            \Event::dispatch('revisionable.created', ['model' => $this, 'revisions' => $revisions]);
        }
    }

    /**
     * If softdeletes are enabled, store the deleted time
     */
    public function postDelete()
    {
        if ((!isset($this->revisionEnabled) || $this->revisionEnabled)
            && $this->isSoftDelete()
            && $this->isRevisionable($this->getDeletedAtColumn())
        ) {
            $revisions[] = [
                'revisionable_type' => $this->getMorphClass(),
                'revisionable_id' => $this->getKey(),
                'key' => $this->getDeletedAtColumn(),
                'old_value' => null,
                'new_value' => $this->{$this->getDeletedAtColumn()},
                'user_type' => $this->getSystemUserType(),
                'user_id' => $this->getSystemUserId(),
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ];

            $revision = new Revision;
            \DB::table($revision->getTable())->insert($revisions);
            \Event::dispatch('revisionable.deleted', ['model' => $this, 'revisions' => $revisions]);
        }
    }

    public function getSystemUserType()
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

    /**
     * @return mixed
     */
    public function revisionHistory()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }
}
