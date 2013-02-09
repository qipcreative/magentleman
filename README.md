Magentleman
===========

Version 0.1.0

A command line interface to Magento, with an interactive console. This is most definitely a work in progress; probably not to be used on a production site as it could be destructive in the wrong hands.

Installation
------------

First, clone this repo somewhere locally. Then copy the files from within ```magento``` directly into your Magento root and it should set things up correctly. I need to test this on a few different systems, as yet, I haven't had time to do that. It's worked for me before on quite a few installs.

There's basically one shell file which goes into your root directory and then a few files which go into the shell directory. I'm going to work on a proper install script one day (or something with modman).

Usage
-----

From your console, simply navigate to your Magento root directory and type the following command:

    php magentleman
  
You'll be greeted with some charming ASCII art and an explanation of the various commands. For example, to fire up a console (probably the coolest feature) you can simply type

    php magentleman console
  
and you'll be ready to rock, IRB style. A lot of stuff is undocumented so far, but I'm working on an extensive walkthrough at http://www.qipcreative.com/magentleman which will show you the ropes.

Please Help!
------------

Have a look at the source code of this bad boy, see what I'm doing wrong and fix it! There's a lot in there that I'm not happy with at the moment, including a lot of duplicated code (especially in the inspector) and no tests (gasp!). If you love Magento as much as I don't, I'd love to hear from you about this script!


