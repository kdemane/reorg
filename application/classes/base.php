<?php

class base
{
    protected $db
            , $debug
            , $id
            , $raw;

    function __construct($db = NULL, $debug = FALSE)
    {
        $this->db = $db;
        $this->debug = $debug;
        //$this->debug = TRUE;

        $this->_init();
    }

    protected function _init()
    {
    }

    protected function _extract_raw_from_payment(&$payment, array $fields, $debug = FALSE)
    {
        $num_expected = count($fields);
        $num_extracted = 0;

        foreach ($fields as $key => $value)
        {
            if ($debug)
            {
                print "???? $key $value\n";
                print $payment->$key . "\n";
            }

            if ($value > 1)
            {
                $found = FALSE;

                for ($i = 1; $i <= $value; $i++)
                {
                    if (!empty($payment->{$key . $i}))
                    {
                        if ($debug)
                            print "Bitch: " . $payment->{$key . $i} . "\n\n";


                        if (is_array($this->raw[$key]))
                            $this->raw[$key][] = $payment->{$key . $i};
                        else
                            $this->raw[$key] = [$payment->{$key . $i}];

                        $found = TRUE;
                    }
                }

                if ($found)
                {
                    if ($debug)
                    {
                        print "Found something multiple\n";
                    }

                    unset($fields[$key]);
                    $num_extracted++;
                }
            }
            else if (!empty($payment->$key))
            {
                if ($debug)
                {
                    print "Found something multiple\n";
                }

                $this->raw[$key] = $payment->$key;
                unset($fields[$key]);
                $num_extracted++;
            }
        }

        if ($this->debug && ($num_expected != $num_extracted))
        {
            $msg = "Extraction failed: $num_expected fields expected, $num_extracted extracted.";

            if (count($fields))
            {
                $msg .= "\nRemaining expected fields:\n  " . implode("\n  ", array_keys($fields)) . "\n";

                $msg .= "Raw input:\n" . print_r($payment, TRUE) . "\n\n";
            }

            //throw new Exception($msg);
            print $msg;
        }

        return $num_extracted;
    }
}

?>