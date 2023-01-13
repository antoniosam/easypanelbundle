<?php
/**
 * User: marcosamano
 * Date: 27/11/18
 */

namespace Ast\EasyPanelBundle\CustomPassword;

class BuildPassword{

    private $salt;
    private $pass = null;

    /**
     * @param $pass
     * @param null $salt
     * @return bool
     */
    public function generate($pass,$salt =null){
        if($pass!=null && $pass !=''){
            $this->salt = is_null($salt)?self::randomSalt():$salt;
            $this->pass = hash('sha512', $this->salt.$pass.$this->salt);
            return true;
        }
        return false;

    }

    /**
     * @return string
     */
    public static function randomSalt(){
        return md5(uniqid(rand(), true));
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param $pass
     * @param null $salt
     * @return mixed
     */
    public static function create($pass,$salt = null){
        $self = new self();
        $self->generate($pass,$salt);
        return $self->getPass();
    }
}