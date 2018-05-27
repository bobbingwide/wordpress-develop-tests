=== wordpress-develop-tests ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: PHPUnit, automated, testing
Requires at least: 4.9
Tested up to: 4.9.1
Stable tag: 4.9.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: default
Domain Path: /languages/

== Description ==
Automated testing of WordPress involves running test cases where manual intervention is not required to run each one.

* WordPress is a complicated system. 
* There aren't as many tests as you might have hoped.
* But the team are working on it.  
* Each new TRAC could/should be backed up by a new test.
 
The purpose of this plugin is twofold:

1. To allow the WP-a2z dynamic API reference parser to be run against the PHPUnit tests for WordPress. Developers can then view the tests and navigate through them as if they were a real plugin.
2. To provide the framework functionality for in situ PHPUnit testing of plugins and themes using oik-batch.



== Installation ==

* Only intended for installation into a WordPress environment where you'll be running your own PHPUnit tests.

* The zip file is built from the tests folder of the wordpress-develop SVN repository.



== Frequently Asked Questions ==


= What does this plugin do? =
Nothing as a plugin. 
You have to invoke the tests using PHPUnit.

From v4.9.1 we provide a modified version of the phpunit.xml file so that you can attempt to run the WordPress core tests in situ.
We'll be running the tests in a Windows 10 environment using PHP 7.2.
The configuration file is different from the one delivered in WordPress core.

Testsuites
The primary directory is different. You run the tests from the plugin directory, 
Instead of being called tests/phpunit/tests the directory is phpunit/tests

We expect PHP 7.0, 7.1 or 7.2 so there's no need for the exclude sections

    <testsuites>
        <!-- Default test suite to run all tests -->
        <testsuite>
            <directory suffix=".php">tests/phpunit/tests</directory>
            <exclude>tests/phpunit/tests/actions/closures.php</exclude>
            <exclude>tests/phpunit/tests/image/editor.php</exclude>
            <exclude>tests/phpunit/tests/image/editorGd.php</exclude>
            <exclude>tests/phpunit/tests/image/editorImagick.php</exclude>
            <exclude>tests/phpunit/tests/oembed/headers.php</exclude>
            <file phpVersion="5.3.0">tests/phpunit/tests/actions/closures.php</file>
            <file phpVersion="5.3.0">tests/phpunit/tests/image/editor.php</file>
            <file phpVersion="5.3.0">tests/phpunit/tests/image/editorGd.php</file>
            <file phpVersion="5.3.0">tests/phpunit/tests/image/editorImagick.php</file>
            <file phpVersion="5.3.0">tests/phpunit/tests/oembed/headers.php</file>
        </testsuite>

Groups - the exclusion list is the same.

    <groups>
        <exclude>
            <group>ajax</group>
            <group>ms-files</group>
            <group>ms-required</group>
            <group>external-http</group>
        </exclude>
    </groups>


Logging - There's no logging section.

    <logging>
        <log type="junit" target="tests/phpunit/build/logs/junit.xml" logIncompleteSkipped="false"/>
    </logging>

We control it from the command line.


Listeners - There's no listener section

	<listeners>
		<listener class="SpeedTrapListener" file="tests/phpunit/includes/speed-trap-listener.php">
			<arguments>
				<array>
					<element key="slowThreshold">
						<integer>150</integer>
					</element>
				</array>
			</arguments>
		</listener>
	</listeners>
	
Filter - There's no filter section - since we don't do code coverage
	
	<filter>
		<whitelist processUncoveredFilesFromWhitelist="true">
			<directory suffix=".php">src</directory>
		</whitelist>
	</filter>
	
PHP const - same as for core
    <php>
        <const name="WP_RUN_CORE_TESTS" value="1" />
    </php>



= What would happen if I did actually run this? =
If you activate the plugin then nothing is expected to happen since it doesn't contain any code. 


If you run PHPUnit then quite a lot might happen. 
And if you had happened to configure your database the same as your live site then you could destroy your live site.
But probably not completely! 



= Can I run the tests from my WordPress site? =
I'm sure you could try to do this but I can't see why you'd want to.

= How do I run in situ tests for my plugin or theme =

Use this plugin in combination with [oik-batch](https://github.com/bobbingwide/oik-batch) 

= What versions of PHPUnit does this support? =

From WordPress 4.8 you can use PHPUnit 6.2, 6.1 or 5.7
For earlier versions of WordPress you can use PHPUnit 5.7



= How does this help me to develop WordPress tests? =

In the same way that WP-a2z.org helps you to develop WordPress code
this provides you with a dynamic reference of current tests.
See [develop.wp-a2z.org](https://develop.wp-a2z.org)


= Have you actually tried using this? =

Yes. oik-plugins are PHPUnit tested using in situ testing. 
See [How to run PHPUnit tests for WordPress plugins in situ](https://herbmiller.me/2016/08/21/run-phpunit-tests-wordpress-plugins-situ/)


= What's the process for managing this repo? =
Method: November 2017

- Extract http://develop.svn.wordpress.org/tags/4.9 to c:\svn\wordpress-develop\4.9

```
cd \svn\wordpress-develop
svn co http://develop.svn.wordpress.org/tags/4.9
```

- remaining steps as August 2016

Method: August 2017
- Extract http://develop.svn.wordpress.org/tags/4.8.1 to c:\svn\wordpress-develop\4.8.1
- remaining steps as August 2016 

Method: June 2017

- Extract http://develop.svn.wordpress.org/trunk to C:\svn\wordpress-develop
- remaining steps as August 2016


Method: March 2017
- Extract http://develop.svn.wordpress.org/tags/4.7.3 to c:\svn\wordpress-develop\4.7.3
- remaining steps as August 2016


Method: December 2016

- Extract http://develop.svn.wordpress.org/branch/4.7 to C:\svn\wordpress-develop\4.7
- remaining steps as August 2016


Method: August 2016

This is the latest process used to build the API reference.

- Extract http://develop.svn.wordpress.org/trunk to C:\svn\wordpress-develop
- Change directory to a clone of the GitHub repository bobbingwide/wordpress-develop-tests
- Remove the phpunit and qunit directories
- Copy files from the tests folder, phpunit and qunit
- Commit changes, including any deletions
- Push to GitHub
- Pull into the WP-a2z websites plugin directory
- Build the dynamic API reference for the latest level


Original method: Nov 2014

To get WP-A2z to work locally I simply created a symbolic link
from the latest SVN version to a plugin directory.

cd wp-content/plugins
mklink /J wordpress-develop-tests svn\wordpress-develop\tests


== Screenshots ==
1. None

== Upgrade Notice ==
= 4.9 = 
Built from http://develop.svn.wordpress.org/tags/4.9 ( on 2017/11/16 )

= 4.8.1 
Built from http://develop.svn.wordpress.org/tags/4.8.3 ( on 2017/08/04 )

= 4.8 =
Built from http://develop.svn.wordpress.org/trunk ( on 2017/06/02 ) 

= 4.7.3 = 
Built from http://develop.svn.wordpress.org/tags/4.7.3 (on 2017/03/12 )

= 4.7 = 
Built from http://develop.svn.wordpress.org/branch/4.7 (on 2016/12/07 )

= 4.6  =
Built from an SVN extract of trunk taken 2016/08/19

= 4.5.3 = 
Built from an SVN extract of trunk taken 2016/07/07

= 4.1-beta1 =
Built from an update performed on 20 Nov 2014

= 4.0 =
* New build from revision 30427 - WordPress 4.0.?

== Changelog ==
= 4.9 = 
* Changed: Source files updated to tags/4.9
* Changed: Updated readme
* Tested: With PHPUnit 6.1
* Tested: With PHP 7.0 and 7.1
* Tested: With WordPress 4.9 and WordPress Multisite 

= 4.8.1 =
* Changed: Source files updated to tags/4.8.3
* Changed: Updated readme
* Tested: With PHPUnit 6.1
* Tested: With PHP 7.0 and 7.1
* Tested: With WordPress 4.8 and 4.8.1

= 4.8 = 
* Changed: Source files updated to version in /trunk = 4.9-alpha-40870-src
* Changed: updated readme
* Tested: With PHPUnit 6.1 and 5.7. 

= 4.7.3 = 
* Changed: Source files updated to tags/4.7.3
* Changed: updated readme
* Deleted: phpunit/tests/user/wpSetCurrentUserhm.php - again, not part of WordPress tests 

= 4.7 = 
* Changed: source files updated to branch/4.7 
* Changed: updated readme
* Deleted: Unnecessary phpunit/build and phpunit/data files - accidentally checked in for 4.5.3
* Deleted: phpunit/tests/user/wpSetCurrentUserhm.php - not part of WordPress tests

= 4.6 =
* Changed: source files updated to latest extract taken on 2016/08/19
* Fixed: Removed unnecessary .idea files 

= 4.5.3 = 
* Changed: source files updated to latest extract taken on 2016/07/07
* Changed: readme should reflect the new build process
* Fixed: Some files were corrupted with local changes 2016/07/09

= 4.1-beta1 =
* Changed: See SVN repository for the change log of the tests

= 4.0 =
* Added: New for develop.wp-a2z.org

== Further reading ==

Extract the latest version of WordPress developer from 

https://develop.svn.wordpress.org/trunk/

This will also extract the latest version of the source.


Read about: 
- [PHPUnit 5.7](https://phpunit.de/manual/5.7/en/index.html)
- [PHPUnit 6.2](https://phpunit.de/manual/6.2/en/index.html) 
- [Automated testing on make.wordpress.org](https://make.wordpress.org/core/handbook/automated-testing)
- [

