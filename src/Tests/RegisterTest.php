<?php
	declare(strict_types=1);
	/**
	 * Created by PhpStorm.
	 * User: newsy
	 * Date: 05/05/2019
	 * Time: 21:10
	 */

	namespace Framework\Tests;

	use Framework\Application\Settings;
	use Framework\Syscrack\Game\Computer;
	use Framework\Syscrack\Game\Internet;
	use Framework\Syscrack\Register;
	use Framework\Syscrack\User;
	use Framework\Syscrack\Verification;

	/**
	 * Class RegisterTest
	 * @package Framework\Tests
	 */
	class RegisterTest extends BaseTestCase
	{

		/**
		 * @var Register
		 */

		protected static $register;

		/**
		 * @var User
		 */

		protected static $user;

		/**
		 * @var Verification
		 */

		protected static $verification;

		/**
		 * @var Internet
		 */

		protected static $internet;

		/**
		 * @var Computer
		 */

		protected static $computer;
		/**
		 * @var string
		 */

		protected static $username = "testaccount";

		/**
		 * @var string
		 */

		protected static $password = "test12345";

		/**
		 * @var string
		 */

		protected static $email = "test@syscrack.co.uk";

		/**
		 * @var int
		 */

		protected static $computerid;

		/**
		 * @var string
		 */
		protected static $token;

		/**
		 *
		 */

		public static function setUpBeforeClass(): void
		{

			if( isset( self::$register ) == false )
				self::$register = new Register();

			if( isset( self::$user) == false )
				self::$user = new User();

			if( isset( self::$verification ) == false )
				self::$verification = new Verification();

			if( isset( self::$computer ) == false )
				self::$computer = new Computer();

			if( isset( self::$internet ) == false )
				self::$internet = new Internet();

			parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
		}

		public static function tearDownAfterClass(): void
		{

			self::$user->delete(self::$user->findByUsername(self::$username));
			self::$computer->delete( self::$computerid );
			parent::tearDownAfterClass(); // TODO: Change the autogenerated stub
		}

		public function testRegister()
		{

			$result = self::$register->register(self::$username, self::$password, self::$email);

			static::assertNotEmpty($result);
			static::assertIsBool($result);

			self::$token = self::$register::$token;
		}

		public function testVerification()
		{

			static::assertNotEmpty(self::$verification);

			if (empty(self::$verification))
				return;

			$userid = self::$verification->getTokenUser(self::$token);
			static::assertTrue(self::$verification->verifyUser(self::$token));
			$computerid = self::$computer->createComputer($userid, Settings::setting('startup_computer'), self::$internet->getIP());
			self::$computerid = $computerid;
			static::assertNotEmpty( $computerid );
			$class = self::$computer->getComputerClass(Settings::setting('startup_computer'));
			static::assertInstanceOf("\Framework\Syscrack\Game\Interfaces\Computer", $class );
			$class->onStartup($computerid, $userid, [], Settings::setting('default_hardware'));
		}
	}
