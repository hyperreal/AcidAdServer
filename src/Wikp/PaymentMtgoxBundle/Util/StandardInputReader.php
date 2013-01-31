<?php

namespace Wikp\PaymentMtgoxBundle\Util;

class StandardInputReader
{
    // @codeCoverageIgnoreStart
    public function getStandardInput()
    {
        return file_get_contents('php://input');
    }
    // @codeCoverageIgnoreEnd
}
