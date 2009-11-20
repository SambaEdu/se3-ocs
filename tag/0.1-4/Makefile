#!/usr/bin/make -f
# Makefile for se3_ocs

all:

install:
    # Add here commands to install the package into debian/Se3.
    # Install des fichiers de configuration et de cron.
	cp -R www/* $(DESTDIR)/var/www/se3/ocsreports/
	cp -R menu/* $(DESTDIR)/var/www/se3/includes/menu.d/
	cp -R install-scripts/* $(DESTDIR)/var/cache/se3_install/
	cp -R perl-scripts/* $(DESTDIR)/var/cache/ocs/
clean:
