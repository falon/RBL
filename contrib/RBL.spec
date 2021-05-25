%global systemd (0%{?fedora} >= 18) || (0%{?rhel} >= 8)
%global upname rbl
%global bigname RBL

Summary: A complete, more than an RBL Management System.
Name: rblmanager
Version: 2.5.0
Release: 1%{?dist}
Group: System Environment/Daemons
License: Apache-2.0
URL: https://falon.github.io/%{bigname}/
Source0: https://github.com/falon/%{bigname}/archive/master.zip
Vendor: Falon Entertainment
Packager: Marco Favero <marco.favero@csi.it>
BuildArch: noarch

# Required for all versions
Requires: httpd >= 2.4.6
Requires: mod_ssl >= 2.4.6
Requires: php >= 7.1
Requires: php-imap >= 7.1
Requires: php-json >= 7.1
Requires: php-ldap >= 7.1
Requires: php-mysqlnd >= 7.1
Requires: php-common >= 7.1
Requires: php-mbstring >= 7.1
Requires: php-gmp >= 7.1
Requires: php-xml >= 7.1
Requires: FalonCommon >= 0.1.1
Requires: systemd-unit-status-email
BuildRequires: php-mysqlnd
BuildRequires: php-gmp
#BuildRequires: composer >= 1.8.0
#Requires: remi-release >= 7.3


%if %systemd
# Required for systemd
%{?systemd_requires}
BuildRequires: systemd
%endif

%description
%{upname} (RBL Manager)
provides an open source Blocklist Management System of various types.
RBLDNS lists, MySQL lists for Postfix or for Amavis too. You can
manage lists of ips, networks, domains, emails or account names.
Every entry has an expiration time. You can manage the entries
manually, or by authomated process from Spam Learning system or
Splunk alert.

%package mailClassifier
Summary: A complete view on authentication and spam classification of your mails.
Group: System Environment/Web
Requires: dspam-client >= 3.10.2
Requires: rblmanager = %{version}-%{release}

%description mailClassifier
Show how your mail are authenticated by DKIM, SPF and DMARC.
View the Spam Classification by Spamassassin and DSPAM. Optionally,
learn your mails using DSPAM Client.


%clean
rm -rf %{buildroot}/

%prep
spectool -R -f -g %{_topdir}/SPECS/RBL.spec
%autosetup -n %{bigname}-master


%install

%if %systemd
mkdir -p %{buildroot}%{_unitdir}
install -m 0644 contrib/systemd/rbl-amavis.service %{buildroot}%{_unitdir}
install -m 0644 contrib/systemd/rbl-amavis.timer %{buildroot}%{_unitdir}
install -m 0644 contrib/systemd/rbl-expire.service %{buildroot}%{_unitdir}
install -m 0644 contrib/systemd/rbl-expire.timer %{buildroot}%{_unitdir}
install -m 0644 contrib/systemd/rbl-ipimap.service %{buildroot}%{_unitdir}
install -m 0644 contrib/systemd/rbl-ipimap.timer %{buildroot}%{_unitdir}
install -m 0644 contrib/systemd/rbl-rbldns@.service %{buildroot}%{_unitdir}
mkdir -p %{buildroot}/usr/bin
sed -i 's|\/usr\/local\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_unitdir}/*.service
%endif
rm -rf contrib/systemd contrib/RPM

# Include dir
mkdir -p %{buildroot}%{_datadir}/include
wget -qO- 'https://github.com/splunk/splunk-sdk-php/archive/1.0.1.tar.gz' | tar xvz -C %{buildroot}%{_datadir}/include

# Web HTTPD conf

install -D -m0444 contrib/%{bigname}.conf-default %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
sed -i 's|\/var\/www\/html\/include|%{_datadir}/include|' %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
sed -i 's|\/var\/www\/html\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
install -D -m0444 contrib/mailClassifier/%{bigname}-mailClassifier.conf %{buildroot}%{_sysconfdir}/httpd/conf.d/
sed -i 's|\/var\/www\/html\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}-mailClassifier.conf

# RBL manager application files
mkdir -p %{buildroot}%{_datadir}/%{bigname}
cp -a * %{buildroot}%{_datadir}/%{bigname}/
mv %{buildroot}%{_datadir}/%{bigname}/imap.conf-default %{buildroot}%{_datadir}/%{bigname}/imap.conf
sed -i 's|\/var\/www\/html\/include|%{_datadir}/include|' %{buildroot}%{_datadir}/%{bigname}/imap.conf
sed -i 's|\/var\/www\/html\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/ipImap/getip.php
mv %{buildroot}%{_datadir}/%{bigname}/config.php-default %{buildroot}%{_datadir}/%{bigname}/config.php
mv %{buildroot}%{_datadir}/%{bigname}/notifyDomains.conf-default %{buildroot}%{_datadir}/%{bigname}/notifyDomains.conf
mv %{buildroot}%{_datadir}/%{bigname}/contrib/splunk/listEmail.conf-default %{buildroot}%{_datadir}/%{bigname}/contrib/splunk/listEmail.conf
mv %{buildroot}%{_datadir}/%{bigname}/template-default %{buildroot}%{_datadir}/%{bigname}/template
mv %{buildroot}%{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php-default  %{buildroot}%{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php
mv %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier/imap.conf-default %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier/imap.conf
sed -i 's|\/var\/www\/html\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/expire.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/rbldns/exportdns.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/splunk/webhook/readPost.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier/index.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier/learn.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier/list.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier/result.php
##Composer requirement
composer --working-dir="%{buildroot}%{_datadir}/%{bigname}" require dautkom/php.ipv4
## Remove unnecessary files
rm %{buildroot}%{_datadir}/%{bigname}/_config.yml %{buildroot}%{_datadir}/%{bigname}/contrib/%{bigname}.conf-default %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier/%{bigname}-mailClassifier.conf %{buildroot}%{_datadir}/%{bigname}/contrib/%{bigname}.spec %{buildroot}%{_datadir}/%{bigname}/vendor/dautkom/php.ipv4/.gitignore %{buildroot}%{_datadir}/%{bigname}/composer.*
rm -rf %{buildroot}%{_datadir}/%{bigname}/vendor/dautkom/php.ipv4/.git

##File list
find %{buildroot}%{_datadir}/%{bigname} -mindepth 1 -type f | grep -v \.conf$ | grep -v \.git | grep -v '\-default$' | grep -v ipImap/report/*\.html | grep -v config\.php | grep -v template/ | grep -v contrib/rbldns/conf\.default | grep -v contrib/mailClassifier | grep -v RBL\.spec | grep -v 'doc/' | grep -v %{bigname}/LICENSE | grep -v %{bigname}/README\.md | grep -v contrib/amavis/exportAmavisLdap\.php | sed -e "s@$RPM_BUILD_ROOT@@" > FILELIST
find %{buildroot}%{_datadir}/%{bigname}/contrib/mailClassifier -mindepth 1 -type f | grep -v \.conf$ | grep -v '\-default$' | sed -e "s@$RPM_BUILD_ROOT@@" > FLMC
mkdir %{buildroot}%{_datadir}/%{bigname}/contrib/rbldns/yourbl

%post
%if %systemd
%systemd_post rbl-expire.timer
%endif
case "$1" in
  2)
	echo -en "\n\n\e[33mRemember to check the changes in imap.conf.\e[39m\n\n"
  ;;
esac

%preun
%if %systemd
%systemd_preun %{upname}-*.service
%systemd_preun %{upname}-*.timer
%endif

%postun
%if %systemd
%systemd_postun_with_restart %{upname}-*.service
%systemd_postun_with_restart %{upname}-*.timer
%endif

%files -f FILELIST
%{_datadir}/include
%{_unitdir}
%dir %{_datadir}/%{bigname}/contrib/rbldns/yourbl
%license %{_datadir}/%{bigname}/LICENSE
%doc %{_datadir}/%{bigname}/README.md
%doc %{_datadir}/%{bigname}/doc
%config(noreplace) %{_datadir}/%{bigname}/config.php
%config(noreplace) %{_sysconfdir}/httpd/conf.d/%{bigname}.conf
%config(noreplace) %{_datadir}/%{bigname}/imap.conf
%config(noreplace) %{_datadir}/%{bigname}/notifyDomains.conf
%config(noreplace) %{_datadir}/%{bigname}/contrib/splunk/listEmail.conf
%config(noreplace) %{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php
%config(noreplace) %{_datadir}/%{bigname}/template/mailWarnHeaders.eml
%config(noreplace) %{_datadir}/%{bigname}/template/mailWarn.eml
%config(noreplace) %{_datadir}/%{bigname}/contrib/rbldns/conf.default

%files mailClassifier -f FLMC
%config(noreplace) %{_sysconfdir}/httpd/conf.d/%{bigname}-mailClassifier.conf
%config(noreplace) %{_datadir}/%{bigname}/contrib/mailClassifier/imap.conf

%changelog
* Tue May 25 2021 Marco Favero <marco.favero@csi.it> 2.5.0-1
- better rbldns management by systemd.

* Wed May 19 2021 Marco Favero <marco.favero@csi.it> 2.5.0-0
- added support for HASHBL with UTF8 characters.

* Thu Feb 11 2021 Marco Favero <marco.favero@csi.it> 2.4.4-7
- removed obsoleted .include directive from systemd units.

* Tue Feb 09 2021 Marco Favero <marco.favero@csi.it> 2.4.4-6
- minor bug fixes

* Tue Feb 09 2021 Marco Favero <marco.favero@csi.it> 2.4.4-5
- removed systemd-email

* Thu Jan 03 2019 Marco Favero <marco.favero@csi.it> 2.4.4-4
- removed unnecessary file style.css
- removed include section in httpd conf file

* Thu Jan 03 2019 Marco Favero <marco.favero@csi.it> 2.4.4-2
- updated dependency composer and dautkom/php.ipv4.
- separated include dir in package falon-common (now required).

* Thu Apr 05 2018 Marco Favero <marco.favero@csi.it> 2.4.4-1
- systemd-email installs as a config file.

* Thu Apr 05 2018 Marco Favero <marco.favero@csi.it> 2.4.4-0
- Minor fixes on SPF Result for mailClassifier
- Added systemd email notifier to all services.

* Fri Feb 16 2018 Marco Favero <marco.favero@csi.it> 2.4.3-0
- Fixed listing domains: recursive check against NS record.

* Thu Feb 15 2018 Marco Favero <marco.favero@csi.it> 2.4.2-0
- Fixed regexp in getDomain function for Learn Tool.
- Added domains exclusion list in imap.conf

* Wed Feb 14 2018 Marco Favero <marco.favero@csi.it> 2.4.1-0
- Fixed regexp in getDomain function for Learn Tool.

* Tue Feb 13 2018 Marco Favero <marco.favero@csi.it> 2.4.0-0
- New version with ability to autolist domains with Learn Tool.

* Thu Jan 25 2018 Marco Favero <marco.favero@csi.it> 2.3.1-0
- New minor version with domain names sanity check

* Thu Dec 21 2017 Marco Favero <marco.favero@csi.it> 2.3-4
- Minor change in presentation, minor fixes

* Thu Dec 21 2017 Marco Favero <marco.favero@csi.it> 2.3-2
- New style for minor fix in mailClassifier

* Wed Dec 20 2017 Marco Favero <marco.favero@csi.it> 2.3-0
- New version with mailClassifier (no other changes)

* Thu Nov 23 2017 Marco Favero <marco.favero@csi.it> 2.2-5
- modified rbl-ipimap.service
- fixed path in getip.php

* Wed Nov 22 2017 Marco Favero <marco.favero@csi.it> 2.2
- Initial version
- Build for 2.2 official version
