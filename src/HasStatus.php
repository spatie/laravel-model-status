<?php


namespace Spatie\LaravelElequentStatus;


trait HasStatus
{

    /** @var string */
    protected $status;


    public function getStatus(){

        return $this->status;
    }


    public function setStatus($status){
        $this->status = $status;
    }



    /**
     * checking to see if the data is valid (to override)
     *
     * @return boolean Returns true if the passed data is valid
     */
    public function isStatusValid(){

        return true;
    }

}