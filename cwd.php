<?php
 
$d = getcwd();
echo "current dir: " . $d . PHP_EOL;
chdir( $d );
$e = getcwd();
echo "current dir: " . $e . PHP_EOL;
if ( $e != $d ) {
  echo "oh my giddy aunt";
}

// Run this routine from a symlinked directory and see what you get.
// 
// Create the symlinked directory using /J for JUNCTION
//
//   cd wp-content\plugins
//   mklink /J wordpress-develop-tests /src/wordpress-develop/tests
//
// Now change to the symlinked directory and run the routine
//   cd wordpress-develop-tests
//   php cwd.php
//
//
// The first getcwd() returns the original source directory 
// after any chdir() you end up in the symlinked directory tree
// 

// Is this to do with the /J switch on mklink? 
/** 

Creates a symbolic link.

MKLINK [[/D] | [/H] | [/J]] Link Target

        /D      Creates a directory symbolic link.  Default is a file
                symbolic link.
        /H      Creates a hard link instead of a symbolic link.
        /J      Creates a Directory Junction.
        Link    specifies the new symbolic link name.
        Target  specifies the path (relative or absolute) that the new link
                refers to.
*/                

