<?php

namespace Tests\Core\Service;

use Core\Entity\AppParameters;
use Core\Exception\SecurityException;
use Core\Handler\SecurityHandler;
use Tests\Core\BaseTest;

/**
 * Class SecurityHandlerTest
 */
class SecurityHandlerTest extends BaseTest
{
    /**
     *
     * @throws SecurityException
     */
    public function testRestrictedDomains()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage("Domain restriction is enabled. The domain you are trying to fetch from is not permitted ");
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('restricted_domains', true);
        $securityHandler = new SecurityHandler($appParameters);

        $securityHandler->checkRestrictedDomains(parent::PNG_TEST_IMAGE);
    }

    /**
     *
     */
    public function testCheckSecurityHashFail()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage(
            "Security Key enabled: Requested URL doesn't match with the hashed Security key !"
        );
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('security_key', 'TestSecurityKey');
        $appParameters->addParameter('security_iv', 'TestSecurityIVXXXX');
        $this->checkSecurityHash($appParameters);
    }

    /**
     *
     */
    public function testSecurityKeyMissing()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage(
            "security_key in empty im parameters.yml!"
        );
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('security_key', '');
        $securityHandler = new SecurityHandler($appParameters);
        $securityHandler->encrypt(parent::OPTION_URL . '/' . parent::JPG_TEST_IMAGE);
    }

    /**
     *
     */
    public function testCheckSecurityIvMissing()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage("Security iv is not set in parameters.yml (security_iv)");
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('security_key', 'TestSecurityKey');
        $appParameters->addParameter('security_iv', '');
        $this->checkSecurityHash($appParameters);
    }

    /**
     *
     * @throws SecurityException
     */
    public function testCheckSecurityHashSuccess()
    {
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('security_key', 'TestSecurityKey');
        $appParameters->addParameter('security_iv', 'TestSecurityIVXXXX');
        $securityHandler = new SecurityHandler($appParameters);
        $options = parent::OPTION_URL;
        $imageSrc = parent::JPG_TEST_IMAGE;
        $hash = $securityHandler->encrypt($options . '/' . $imageSrc);
        list($hashedOptions, $hashedImageSrc) = $securityHandler->checkSecurityHash($hash, $imageSrc);
        $this->assertEquals($hashedOptions, $options);
        $this->assertEquals($hashedImageSrc, $imageSrc);
    }

    /**
     *
     */
    public function testEncryptionDecryption()
    {
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('security_key', 'TestSecurityKey');
        $appParameters->addParameter('security_iv', 'TestSecurityIVXXXX');

        $securityHandler = new SecurityHandler($appParameters);
        $randomString = str_shuffle('AKALEOCJCNXMSOLWO5#KXMw');
        $hashedString = $securityHandler->encrypt($randomString);
        $decryptedString = $securityHandler->decrypt($hashedString);
        $this->assertEquals($decryptedString, $randomString);
    }

    /**
     *
     */
    public function testEncryptionEmptyDecryption()
    {

        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('security_key', 'TestSecurityKey');
        $appParameters->addParameter('security_iv', 'TestSecurityIVXXXX');
        $securityHandler = new SecurityHandler($appParameters);
        $options = '';
        $imageSrc = '';
        $hash = $securityHandler->encrypt($options . '/' . $imageSrc);
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage("Something went wrong when decrypting the hashed URL: " . $hash);

        $securityHandler->checkSecurityHash($hash, $imageSrc);
    }

    /**
     * @param AppParameters $appParameters
     * @throws SecurityException
     */
    protected function checkSecurityHash($appParameters): void
    {
        $securityHandler = new SecurityHandler($appParameters);
        $options = parent::OPTION_URL;
        $imageSrc = parent::JPG_TEST_IMAGE;
        $securityHandler->checkSecurityHash($options, $imageSrc);
    }
}
