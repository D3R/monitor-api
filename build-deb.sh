#!/bin/bash
#
# Debian package script
#

BUILD_DIR="./dpkg/build"
PACKAGE_DIR="./dpkg/package/"
DEBIAN_DIR="./DEBIAN"
DPKG="/usr/bin/fakeroot /usr/bin/dpkg-deb --build "
SCP="/usr/bin/scp"
SCPDEST="siteadmin@apt.d3r.com:/clients/apt/live/incoming/"
REPO_REFRESH="ssh siteadmin@apt.d3r.com \"cd /clients/apt && ./repository-import.sh && ./sync-to-s3.sh\""
CP="/bin/cp -R"
RM="/bin/rm -f"
GIT=`which git`

err() {
    out "\033[1;31m** $1\033[0m"
}

msg(){
    out "-- $1"
}

question(){
    out "\033[0;33m!! $1 ($2)\033[0m"
}

out(){
    echo -e " $1"
}

check(){
    RET=$1
    CMD=$2
    if [ 0 -ne $RET ]; then
        err "Error - $2 failed"
        exit 1
    fi
}

DIST="$1"
if [ -z $DIST ]; then
    echo "Usage: $0 <lenny|squeeze|wheezy|etc>"
    exit 1
fi

# Copy the correct control file into the debian directory
if [ ! -e ./support/control.$DIST ]; then
    err "Unknown distribution $DIST"
    exit 1
fi

msg "clean up old deb packages"
rm -f d3r-tools*.deb
check $? rm

if [ -d $BUILD_DIR ]; then
    question "$BUILD_DIR exists - I'll have to remove it - continue?" "y/n"
    read YN
    if [ "$YN" = "n" ] || [ "$YN" = "N" ]; then
        msg "bye"
        exit 0
    fi
    msg "remove old build directory"
    rm -fR $BUILD_DIR
    check $? rm
fi

msg "create build directory $BUILD_DIR"
mkdir $BUILD_DIR
check $? "mkdir $BUILD_DIR"
mkdir $BUILD_DIR/DEBIAN
check $? "mkdir $BUILD_DIR/DEBIAN"

msg "copying postinst file"
cp support/DEBIAN.postinst $BUILD_DIR/DEBIAN/postinst
check $? "mkdir $BUILD_DIR/DEBIAN/postinst"
chmod 0755 $BUILD_DIR/DEBIAN/postinst
check $? "chmod $BUILD_DIR/DEBIAN/postinst"

VERSION=`cat control.version`
msg "starting build for distribution $DIST @ version $VERSION"

msg "installing distribution control file control.$DIST"
/bin/cat support/control.$DIST | /bin/sed "s/%VERSION%/$VERSION/g" > $BUILD_DIR/DEBIAN/control

msg "make build filesystem"
mkdir -p $BUILD_DIR/usr/lib/d3r-monitor-api

msg "copy source files to build directory"
files=( index.php library vendor config support/virtualhost.conf)
for file in "${files[@]}"; do
    msg "copying $file"
    $CP "$file" $BUILD_DIR/usr/lib/d3r-monitor-api/
    check $? "copy $file"
done

# rm $BUILD_DIR/usr/lib/d3r-monitor-api/config/config.php

msg "generating debian package"
if [ -d $PACKAGE_DIR ]; then
    question "$PACKAGE_DIR exists - I'll have to remove it - continue?" "y/n"
    read YN
    if [ "$YN" = "n" ] || [ "$YN" = "N" ]; then
        msg "bye"
        exit 0
    fi
    msg "remove old package output directory"
    rm -fR $PACKAGE_DIR
    check $? "rm $PACKAGE_DIR"
fi
mkdir $PACKAGE_DIR
check $? "mkdir $PACKAGE_DIR"
NAME=`$DPKG $BUILD_DIR $PACKAGE_DIR`
check $? dpkg

NAME=`echo "$NAME" | awk -F' in ' '{print $2}' | sed "s/'//g" | sed 's/\`//g' | sed "s/\.$//"`
msg "package name is $NAME"

question "copy debian file to server?" "y/n"
read YN
if [ "$YN" = "Y" ] || [ "$YN" = "y" ]; then
    cp $NAME /clients/apt/live/incoming/
    check $? cp
fi
