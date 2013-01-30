<?php

namespace Wikp\PaymentMtgoxBundle\Util;

class StandardInputReader
{
    public function getStandardInput()
    {
        return file_get_contents('php://stdin');
    }
}
