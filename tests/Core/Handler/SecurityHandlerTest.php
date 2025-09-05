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
     * Test that local file paths are allowed (no domain restriction)
     *
     * @throws SecurityException
     */
    public function testLocalFilePathsAllowed()
    {
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('restricted_domains', true);
        $securityHandler = new SecurityHandler($appParameters);

        // Should not throw exception for local file paths
        $this->expectNotToPerformAssertions();
        $securityHandler->checkRestrictedDomains(parent::PNG_TEST_IMAGE);
    }

    /**
     * Test restricted domains with invalid URL
     *
     * @throws SecurityException
     */
    public function testRestrictedDomainsWithInvalidUrl()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage("The domain you are trying to fetch from is not permitted: forbidden.com");
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('restricted_domains', true);
        $securityHandler = new SecurityHandler($appParameters);

        $securityHandler->checkRestrictedDomains('https://forbidden.com/image.jpg');
    }

    /**
     * Test exact domain matching
     *
     * @throws SecurityException
     */
    public function testExactDomainMatch()
    {
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('restricted_domains', true);
        $appParameters->addParameter('whitelist_domains', ['example.com', 'test.org']);
        $securityHandler = new SecurityHandler($appParameters);

        // Should not throw exception for exact matches
        $this->expectNotToPerformAssertions();
        $securityHandler->checkRestrictedDomains('https://example.com/image.jpg');
        $securityHandler->checkRestrictedDomains('https://test.org/image.png');
    }

    /**
     * Test wildcard domain matching
     *
     * @throws SecurityException
     */
    public function testWildcardDomainMatch()
    {
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('restricted_domains', true);
        $appParameters->addParameter('whitelist_domains', ['*.example.com', '*.test.org']);
        $securityHandler = new SecurityHandler($appParameters);

        // Should not throw exception for wildcard matches
        $this->expectNotToPerformAssertions();
        $securityHandler->checkRestrictedDomains('https://subdomain.example.com/image.jpg');
        $securityHandler->checkRestrictedDomains('https://api.test.org/image.png');
        $securityHandler->checkRestrictedDomains('https://cdn.example.com/image.gif');
    }

    /**
     * Test wildcard domain rejection
     *
     * @throws SecurityException
     */
    public function testWildcardDomainRejection()
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage("The domain you are trying to fetch from is not permitted: other.com");

        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('restricted_domains', true);
        $appParameters->addParameter('whitelist_domains', ['*.example.com']);
        $securityHandler = new SecurityHandler($appParameters);

        // Should throw exception for non-matching domains
        $securityHandler->checkRestrictedDomains('https://other.com/image.jpg');
    }

    /**
     * Test mixed exact and wildcard domain matching
     *
     * @throws SecurityException
     */
    public function testMixedDomainMatching()
    {
        /** @var AppParameters $appParameters */
        $appParameters = clone $this->app['params'];
        $appParameters->addParameter('restricted_domains', true);
        $appParameters->addParameter('whitelist_domains', ['example.com', '*.test.org', 'specific.domain.com']);
        $securityHandler = new SecurityHandler($appParameters);

        // Should not throw exception for any of these
        $this->expectNotToPerformAssertions();
        $securityHandler->checkRestrictedDomains('https://example.com/image.jpg');
        $securityHandler->checkRestrictedDomains('https://subdomain.test.org/image.png');
        $securityHandler->checkRestrictedDomains('https://specific.domain.com/image.gif');
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
