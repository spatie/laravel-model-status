<?php

return [

    /*
     * The class name of the status model that holds all statuses.
     *
     * The model must be or extend `Spatie\ModelStatus\Status`.
     */
    'status_model' => Spatie\ModelStatus\Status::class,

    /*
     * The name of the column which holds the ID of the model related to the statuses.
     *
     * You can change this value if you have set a different name in the migration for the statuses table.
     */
    'model_primary_key_attribute' => 'model_id',

    /*
     * Control if the model that has the HasStatuses Trait should be touched when setting a Status.
     * Touched refers: to update the model's updated_at timestamp column.
     */
    'touches_model' => false,
];
