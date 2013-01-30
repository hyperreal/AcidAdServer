<?php

namespace Wikp\PaymentMtgoxBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use JMS\Payment\CoreBundle\Model\PaymentInstructionInterface;

class IpnControllerTest extends WebTestCase
{
    private $paymentInstructionMock;
    private $ipnParams;
    private $expectedApproveCount;
    private $expectedCancelCount;

    public function testValidRequest()
    {
        $this->setIpnParams();
        $client = static::createClient();
        $this->expectedApproveCount = 1;
        $this->expectedCancelCount = 0;

        $this->performRequest($client);

        $this->assertEquals('[OK]', $client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testPartialRequest()
    {
        $this->setIpnParams('partial');
        $client = static::createClient();
        $this->expectedApproveCount = 0;
        $this->expectedCancelCount = 0;

        $crawler = $this->performRequest($client);

        $this->assertTrue($crawler->filter('html:contains("partial payments not supported")')->count() == 1);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCancelRequest()
    {
        $this->setIpnParams('cancelled');
        $client = static::createClient();
        $this->expectedApproveCount = 0;
        $this->expectedCancelCount = 1;

        $this->performRequest($client);

        $this->assertEquals('[OK]', $client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testInvalidRequest()
    {
        $this->setIpnParams('invalid status');
        $client = static::createClient();
        $this->expectedApproveCount = 0;
        $this->expectedCancelCount = 0;

        $this->performRequest($client);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    private function performRequest($client)
    {
        $this->setUpServices($client);

        $crawler = $client->request(
            'POST',
            '/mtgox/ipn',
            $this->getIpnParams(),
            array(),
            $this->getValidHeaders(
                $client->getContainer()->getParameter('mtgox_api_key'),
                $client->getContainer()->getParameter('mtgox_api_secret')
            )
        );

        return $crawler;
    }

    private function setUpServices($client)
    {
        $client->getContainer()->set(
            'wikp_payment_mtgox.stdin_reader',
            $this->getStdinMock($this->getRawValidParams())
        );

        $client->getContainer()->set(
            'wikp_payment_mtgox.order_repository',
            $this->getOrderRepositoryFactoryMock()
        );

        $client->getContainer()->set(
            'payment.plugin_controller',
            $this->getPluginControllerMock()
        );

        $client->getContainer()->set(
            'doctrine.orm.entity_manager',
            $this->getEntityManagerMock()
        );
    }

    private function getPluginControllerMock()
    {
        $mock = $this->getMockBuilder('JMS\Payment\CoreBundle\PluginController\EntityPluginController')
            ->setMethods(array('approveAndDeposit', 'createPayment'))
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('approveAndDeposit')
            ->will($this->returnValue($this->getValidResultMock()));

        $mock->expects($this->any())
            ->method('createPayment')
            ->will($this->returnValue($this->getPaymentMock()));

        return $mock;
    }

    private function getPaymentMock()
    {
        $mock = $this->getMockBuilder('\JMS\Payment\CoreBundle\Entity\Payment')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function getValidResultMock()
    {
        $result = $this->getMockBuilder('JMS\Payment\CoreBundle\PluginController\Result')
            ->setMethods(array('getStatus', 'getPluginException'))
            ->disableOriginalConstructor()
            ->getMock();

        $result->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(\JMS\Payment\CoreBundle\PluginController\Result::STATUS_SUCCESS));

        $result->expects($this->any())
            ->method('getPluginException')
            ->will($this->returnValue(null));

        return $result;
    }

    private function getEntityManagerMock()
    {
        $emMock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->setMethods(array('getClassMetadata', 'persist', 'flush', 'clear'))
            ->disableOriginalConstructor()
            ->getMock();

        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $emMock->expects($this->any())
            ->method('clear')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $emMock;
    }

    private function getOrderRepositoryFactoryMock()
    {
        $factoryMock = $this->getMockBuilder('Wikp\PaymentMtgoxBundle\Plugin\OrderRepositoryFactory')
            ->setMethods(array('getRepository'))
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getOrderRepositoryMock()));

        return $factoryMock;
    }

    private function getOrderRepositoryMock()
    {
        $orderMock = $this->getOrderMock();

        $repositoryMock = $this->getMockForAbstractClass(
            'Wikp\PaymentMtgoxBundle\Plugin\OrderRepositoryInterface',
            array('getOrderById', 'find')
        );

        $repositoryMock->expects($this->any())
            ->method('getOrderById')
            ->will($this->returnValue($orderMock));

        $repositoryMock->expects($this->any())
            ->method('find')
            ->will($this->returnValue($orderMock));

        return $repositoryMock;
    }

    private function getOrderMock()
    {
        $orderMock = $this->getMock(
            'Wikp\PaymentMtgoxBundle\Plugin\OrderInterface',
            array(
                'getPaymentInstruction',
                'cancel',
                'approve',
                'getId'
            )
        );

        $orderMock->expects($this->any())
            ->method('getPaymentInstruction')
            ->will($this->returnValue($this->getPaymentInstructionMock()));

        $orderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('119'));

        $orderMock->expects($this->exactly($this->expectedApproveCount))
            ->method('approve');

        $orderMock->expects($this->exactly($this->expectedCancelCount))
            ->method('cancel');

        return $orderMock;
    }

    private function getPaymentInstructionMock()
    {
        if (!empty($this->paymentInstructionMock)) {
            return $this->paymentInstructionMock;
        }

        $this->paymentInstructionMock = $this->getMockBuilder('JMS\Payment\CoreBundle\Entity\PaymentInstruction')
            ->setMethods(
                array(
                    'getPendingTransaction',
                    'getState'
                )
            )
            ->setConstructorArgs(
                array(
                    100,
                    'USD',
                    \Wikp\PaymentMtgoxBundle\Plugin\MtgoxPaymentPlugin::SYSTEM_NAME
                )
            )
            ->getMock();

        $this->paymentInstructionMock->expects($this->any())
            ->method('getPendingTransaction')
            ->will($this->returnValue(null));

        $this->paymentInstructionMock->expects($this->any())
            ->method('getState')
            ->will($this->returnValue(PaymentInstructionInterface::STATE_VALID));

        return $this->paymentInstructionMock;
    }

    private function getRawValidParams()
    {
        return http_build_query(
            $this->getIpnParams(),
            '',
            '&'
        );
    }

    private function getStdinMock($content)
    {
        $stdin = $this->getMock('Wikp\\PaymentMtgoxBundle\\Util\StandardInputReader', array('getStandardInput'));

        $stdin->expects($this->any())
            ->method('getStandardInput')
            ->will($this->returnValue($content));

        return $stdin;
    }

    private function setIpnParams($status = 'paid')
    {
        $this->ipnParams = array(
            'id' => 1,
            'payment_id' => '12312-901231',
            'data' => '119',
            'date' => '2012-02-01 23:32:11',
            'amount' => '100',
            'currency' => 'BTC',
            'method' => 'MTGOXBTC',
            'status' => $status,
            'aonce' => 11,
        );
    }

    private function getIpnParams()
    {
        return $this->ipnParams;
    }

    private function getValidHeaders($key, $secret)
    {
        return array(
            'HTTP_REST_SIGN' => $this->getRestSign($this->getIpnParams(), $secret),
            'HTTP_REST_KEY' => $key,
        );
    }

    private function getRestSign($params, $secret)
    {
        return base64_encode(
            hash_hmac(
                'sha512',
                http_build_query($params, '', '&'),
                base64_decode($secret),
                true
            )
        );
    }
}
