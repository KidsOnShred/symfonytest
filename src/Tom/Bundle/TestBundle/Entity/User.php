<?php 
namespace Tom\Bundle\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="testusers")
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

    /*===SALT===
    Encryption key to make passwords more secure. 
    DO NOT CHANGE THIS!
    */
    const SALT = "#!3nCrYpt3d!#";

    public function __construct($data)
    {
        extract($data);

        $this->setEmail($email);
        $this->setPassword($password);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $this->convertToMd5($password);
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }


    public function checkPassword($password)
    {
        if ($this->password == $this->convertToMd5($password))
            return true;
        return false;
    }

    public function convertToMd5($string)
    {
        return md5($string . self::SALT);
    }
}
