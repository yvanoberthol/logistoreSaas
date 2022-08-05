<?php


namespace App\Form\DataTransformer;

use DateTime;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class LocaleToDateTimeTransformer implements DataTransformerInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * LocaleToDateTimeTransformer constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }


    /**
     * @inheritDoc
     */
    public function transform($date): ?string
    {
        $format = $this->session
            ->get('setting')->getDateMedium();

        return ($date === null)?'': $date->format($format);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($localDate): ?DateTime
    {
        if ($localDate === null){
            throw new TransformationFailedException('you should enter a date');
        }

        $date = DateTime::createFromFormat($this->session
            ->get('setting')->getDateMedium(), $localDate);

        if ($date === false){
            throw new TransformationFailedException("the format is not valid");
        }

        return $date;
    }
}
