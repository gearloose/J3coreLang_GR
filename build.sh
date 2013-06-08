#!/bin/bash

rm -rf release
mkdir release

pushd site/el-GR
cp ../../xml/site.el-GR.xml el-GR.xml
cp ../../xml/site.install.xml install.xml
zip -r ../../release/site_el-GR.zip *
popd

pushd admin/el-GR
cp ../../xml/admin.el-GR.xml el-GR.xml
cp ../../xml/admin.install.xml install.xml
zip -r ../../release/admin_el-GR.zip *
popd

pushd install/el-GR
cp ../../xml/install.el-GR.xml el-GR.xml
zip -r ../../release/install_el-GR.zip *
popd

cd release
cp ../pkg_el-GR.xml .
zip pkg_el-GR.zip site_el-GR.zip admin_el-GR.zip pkg_el-GR.xml
rm pkg_el-GR.xml
