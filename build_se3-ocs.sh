#!/bin/bash
# $Id$
DISTRIB=$1
BRANCHE=$2
[ "$BRANCHE" != "stable" ] && OPT="XP"

if [ "$BRANCHE" == "" -o "$DISTRIB" == "" ]; then
echo "usage :  $0 distrib branche
distrib : lenny ou etch
branche : stable ou xp"
exit 1
fi
set -e 
SE3MODULE="se3-ocs"
PATH_SVN_LOCAL="/digloo/deb/se3/"
PATH_SE3MODULE="${PATH_SVN_LOCAL}${SE3MODULE}/trunk"


SOURCE_DIR="sources"
#Couleurs
COLTITRE="\033[1;35m"	# Rose
COLPARTIE="\033[1;34m"	# Bleu

COLTXT="\033[0;37m"	# Gris
COLCHOIX="\033[1;33m"	# Jaune
COLDEFAUT="\033[0;33m"	# Brun-jaune
COLSAISIE="\033[1;32m"	# Vert

COLCMD="\033[1;37m"	# Blanc

COLERREUR="\033[1;31m"	# Rouge
COLINFO="\033[0;36m"	# Cyan


ERREUR()
{
echo -e "$COLERREUR"
echo "ERREUR!"
echo -e "$1"
echo -e "$COLTXT"
exit 1
}



POURSUIVRE()
{
	REPONSE=""
	while [ "$REPONSE" != "o" -a "$REPONSE" != "O" -a "$REPONSE" != "n" ]
	do
		#echo -e "$COLTXT"
		echo -e "${COLTXT}Peut-on poursuivre ? (${COLCHOIX}O/n${COLTXT}) $COLSAISIE\c"
		read REPONSE
		if [ -z "$REPONSE" ]; then
			REPONSE="o"
		fi
	done
echo -e "$COLTXT"
	if [ "$REPONSE" != "o" -a "$REPONSE" != "O" ]; then
		ERREUR "Abandon!"
	fi
}

svn update $PATH_SE3MODULE || exit 1
rm -rf ./workdir
mkdir -p ./workdir/
cp -a "$PATH_SE3MODULE"/* ./workdir/
cd ./workdir/

echo "Suppression reps .svn"
POURSUIVRE
find ./ -name .svn -print0 | xargs -0 rm -r

# echo "traitement utf8 pour etch"
# if [ "$DISTRIB" == "etch" ]; then
# A=`find ./$SE3MODULE -iname "*.sh" -type f`
# 	        for FICH in $A
# 		do
# 			recode latin9..utf8 $FICH
# 			echo "$FICH-->ok"
# 		done
# fi

echo "construction du paquet $SE3MODULE"
POURSUIVRE
# cd $SOURCE_DIR
# chmod +x ./scripts/*
dh_clean
debuild -uc -us -b
# cd ..
# mv *.deb ..
# cd ..
# rm -rf workdir


echo "copie sur le dépot se3$OPT du paquet $SE3MODULE pour la branche $BRANCHE"
POURSUIVRE
# scp -P 2222 $SE3MODULE*.deb root@wawadeb:/var/ftp/debian/dists/stable/se3XP/binary-i386/net/

cd ..
if [ "$DISTRIB" == "etch" -o "$DISTRIB" == "all" ]; then
	scp -P 2222 $SE3MODULE*.deb root@wawadeb:/var/ftp/debian/dists/etch/se3$OPT/binary-i386/net/
	[ "$BRANCHE" == "all" ] && scp -P 2222 $SE3MODULE*.deb root@wawadeb:/var/ftp/debian/dists/etch/se3/binary-i386/net/
	
fi

if [ "$DISTRIB" == "lenny" -o "$DISTRIB" == "all" ]; then
	 scp -P 2222 $SE3MODULE*.deb root@wawadeb:/var/ftp/debian/dists/lenny/se3$OPT/binary-i386/net/
	 [ "$BRANCHE" == "all" ] && scp -P 2222 $SE3MODULE*.deb root@wawadeb:/var/ftp/debian/dists/stable/se3/binary-i386/net/
fi
exit 0