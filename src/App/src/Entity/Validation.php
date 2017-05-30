<?php
/**
 * Created by PhpStorm.
 * User: jamesn
 * Date: 5/12/17
 * Time: 1:54 PM
 */
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ValidationRepository")
 * @ORM\Table(name="validations")
 */
class Validation
{
    use EntityTrait;

    /**
     * @ORM\Column(type="string", length=50, unique=true, nullable=false)
     */
    private $parameter;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=false)
     */
    private $expression;

    /**
     * @ORM\Column(type="integer", unique=true, nullable=false)
     */
    private $error_code;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    public function getParameter()
    {
        return $this->parameter;
    }

    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }

    public function setErrorCode($code)
    {
        $this->error_code = $code;
    }

}