<?php namespace TevoHarvester\Tevo;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Model extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes excluded from the model’s JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     * This allows us to save an item before deleting. It copies the
     * save() process including performUpdate() or performInsert()
     * so that we can do either of those without firing the associated
     * events.
     *
     * Finally, it calls the standard delete(), which WILL fire the
     * ‘deleting’ and ‘deleted’ events.
     *
     * This is useful because when fetching in item from the API
     * it is possible to receive the item as deleted even though it
     * was never previously saved. If the item did not exist and was not
     * saved before deleting there would be no record of that item.
     *
     * @return bool|null
     */
    protected function saveThenDelete(array $options = array())
    {
        $query = $this->newQueryWithoutScopes();

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
//            $saved = $this->performUpdate($query, $options);
            $dirty = $this->getDirty();

            if (count($dirty) > 0) {
                // First we need to create a fresh query instance and touch the creation and
                // update timestamp on the model which are maintained by us for developer
                // convenience. Then we will just continue saving the model instances.
                if ($this->timestamps && array_get($options, 'timestamps', true)) {
                    $this->updateTimestamps();
                }

                // Once we have run the update operation, we will fire the "updated" event for
                // this model instance. This will allow developers to hook into these after
                // models are updated, giving them a chance to do any special processing.
                $dirty = $this->getDirty();

                if (count($dirty) > 0) {
                    $this->setKeysForSaveQuery($query)->update($dirty);
                }
            }
        } // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
//            dd('$this does not exist');
//            $saved = $this->performInsert($query, $options);

            // First we'll need to create a fresh query instance and touch the creation and
            // update timestamps on this model, which are maintained by us for developer
            // convenience. After, we will just continue saving these model instances.
            if ($this->timestamps && array_get($options, 'timestamps', true)) {
                $this->updateTimestamps();
            }

            // If the model has an incrementing key, we can use the "insertGetId" method on
            // the query builder, which will give us back the final inserted ID for this
            // table from the database. Not all tables have to be incrementing though.
            $attributes = $this->attributes;

            if ($this->incrementing) {
                $this->insertAndSetId($query, $attributes);
            } // If the table is not incrementing we'll simply insert this attributes as they
            // are, as this attributes arrays must contain an "id" column already placed
            // there by the developer as the manually determined key for these models.
            else {
                $query->insert($attributes);
            }

            // We will go ahead and set the exists property to true, so that it is set when
            // the created event is fired, just in case the developer tries to update it
            // during the event. This will allow them to do so and run an update here.
            $this->exists = true;
        }

        $this->syncOriginal();

        if (array_get($options, 'touch', true)) {
            $this->touchOwners();
        }

        return $this->delete();
    }


    /**
     * Find a model by its primary key.
     *
     * @param  mixed $id
     * @param  array $columns
     *
     * @return \Illuminate\Support\Collection|static|null
     */
    public static function findWithTrashed($id, $columns = array('*'))
    {
        $instance = new static;

        if (is_array($id) && empty($id)) {
            return $instance->newCollection();
        }

        return $instance->newQueryWithoutScope(new SoftDeletingScope)->find($id, $columns);
    }


    /**
     * Find a model by its primary key or return new static.
     *
     * @param  mixed $id
     * @param  array $columns
     *
     * @return \Illuminate\Support\Collection|static
     */
    public static function findOrNewWithTrashed($id, $columns = array('*'))
    {
        if (!is_null($model = static::findWithTrashed($id, $columns))) {
            return $model;
        }

        return new static;
    }

    /**
     * Scope a query to only include active items.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('deleted_at', null);
    }
}
