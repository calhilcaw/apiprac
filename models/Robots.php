<?php

namespace Store\Toys;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;

class Robots extends Model
{
    public function validation()
    {
        $validator = new Validation();
        
        $validator->add(
            "type",
            new InclusionIn(
                [
                    "model" => $this,
                       "domain" => [
                           "droid",
                           "mechanical",
                           "virtual",
                        ]
                ]
            )
        );
        
        /* $this->validate(
            new InclusionIn(
                [
                   "model" => $this,
                   "domain" => [
                       "droid",
                       "mechanical",
                       "virtual",
                    ]
                ]
            )    
        );*/
        
        $validator->add(
            "name",
             new Uniqueness(
                [
                    "model" => $this,
                    "message" => "The robots name must be unique",
                ]  
            ) 
        );
        
        /*$this->validate(
            new Uniqueness(
                [
                    "field" => "name",
                    "message" => "The robots name must be unique",
                ]  
            ) 
        );*/
        
        if($this->year < 0) {
            $this->appendMessage(
                new Message("This year cannot be less that zero")  
            );
        }
        
        return $this->validate($validator);
    }
}