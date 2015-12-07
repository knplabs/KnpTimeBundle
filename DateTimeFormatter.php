<?php

namespace Knp\Bundle\TimeBundle;

use Symfony\Component\Translation\TranslatorInterface;
use Datetime;

class DateTimeFormatter
{
    protected $translator;

    /**
     * Constructor
     *
     * @param  TranslatorInterface $translator Translator used for messages
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns a formatted diff for the given from and to datetimes
     *
     * @param  Datetime $from
     * @param  Datetime $to
     *
     * @return string
     */
    public function formatDiff(Datetime $from, Datetime $to, $precision = array())
    {
        $index        = 0;
        $firtsMessage = true;
        $diffMessage  = '';

        static $units = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        );

        $diff = $to->diff($from);

        foreach ($units as $attribute => $unit) {
            $count = $diff->$attribute;

            if( count($precision) === 0 ) {
                if (0 !== $count) {
                    return $this->doGetDiffMessage($count, $diff->invert, $unit);
                }
            } else {
                if( in_array($attribute, $precision) && 0 !== $count ) {
                    $diffMessage .= ($index === 0 ? '' : ' ').$this->doGetDiffMessage($count, $diff->invert, $unit, $firtsMessage);

                    $firtsMessage = false;
                    $index++;
                }
            }
        }

        if($index === 0) {
            $diffMessage = $this->getEmptyDiffMessage();
        }

        return $diffMessage;
    }

    /**
     * Returns the diff message for the specified count and unit
     *
     * @param  integer $count  The diff count
     * @param  boolean $invert Whether to invert the count
     * @param  integer $unit   The unit must be either year, month, day, hour,
     *                         minute or second
     *
     * @return string
     */
    public function getDiffMessage($count, $invert, $unit)
    {
        if (0 === $count) {
            throw new \InvalidArgumentException('The count must not be null.');
        }

        $unit = strtolower($unit);

        if (!in_array($unit, array('year', 'month', 'day', 'hour', 'minute', 'second'))) {
            throw new \InvalidArgumentException(sprintf('The unit \'%s\' is not supported.', $unit));
        }

        return $this->doGetDiffMessage($count, $invert, $unit);
    }

    protected function doGetDiffMessage($count, $invert, $unit, $firtsMessage = true)
    {
        $id = sprintf('diff.%s.%s', $invert ? 'ago' : 'in', $unit);

        if(!$firtsMessage) {
            $id = str_replace(array('.ago','.in'), '', $id);
        }

        return $this->translator->transChoice($id, $count, array('%count%' => $count), 'time');
    }

    /**
     * Returns the message for an empty diff
     *
     * @return string
     */
    public function getEmptyDiffMessage()
    {
        return $this->translator->trans('diff.empty', array(), 'time');
    }
}
