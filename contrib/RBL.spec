%global systemd (0%{?fedora} >= 18) || (0%{?rhel} >= 7)
%global upname rbl
%global bigname RBL

Summary: A complete, more than an RBL Management System.
Name: rblmanager
Version: 2.2
Release: 0.1%{?dist}
Group: System Environment/Daemons
License: Apache
URL: https://falon.github.io/%{bigname}/
Source0: https://github.com/falon/%{bigname}/archive/master.zip
BuildArch:	noarch

# Required for all versions
Requires: httpd >= 2.4.6
Requires: mod_ssl >= 2.4.6
Requires: php >= 7.1
Requires: php-imap >= 7.1
Requires: php-json >= 7.1
Requires: php-ldap >= 7.1
Requires: php-mysqlnd >= 7.1
Requires: php-gmp >= 7.1
Requires: composer >= 2.5.2
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



%prep


%install

cd %{bigname}-master
%if %systemd
mkdir -p %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-amavis.service %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-amavis.timer %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-expire.service %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-expire.timer %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-ipimap.service %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-ipimap.timer %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-rbldns@.service %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-rbldns@spamip.service %{buildroot}%{_unitdir}
install -m 0755 contrib/systemd/rbl-rbldns@whiteip.service %{buildroot}%{_unitdir}
sed -i 's|\/usr\/local\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_unitdir}/*.service
%endif

# Include dir
mkdir -p %{buildroot}%{_datadir}/include
install -m0444 ajaxsbmt.js %{buildroot}%{_datadir}/include
install -m0444 pleasewait.gif %{buildroot}%{_datadir}/include
wget -qO- 'https://github.com/splunk/splunk-sdk-php/archive/1.0.1.tar.gz' | tar xvz -C %{buildroot}%{_datadir}/include
install -m0444 style.css  %{buildroot}%{_datadir}/include

# Web HTTPD conf
install -m0444 contrib/%{bigname}.conf-default %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
sed -i 's|\/var\/www\/html\/include|%{_datadir}/include|' %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
sed -i 's|\/var\/www\/html\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf

# RBL manager application files
cp -a * %{buildroot}%{_datadir}/%{bigname}/
cp -p %{buildroot}%{_datadir}/%{bigname}/imap.conf-default %{buildroot}%{_datadir}/%{bigname}/imap.conf
sed -i 's|\/var\/www\/html\/include|%{_datadir}/include|' %{buildroot}%{_datadir}/%{bigname}/imap.conf
cp -p %{buildroot}%{_datadir}/%{bigname}/config.php-default %{buildroot}%{_datadir}/%{bigname}/config.php
cp -p %{buildroot}%{_datadir}/%{bigname}/notifyDomains.conf-default %{buildroot}%{_datadir}/%{bigname}/notifyDomains.conf
cp -p %{buildroot}%{_datadir}/%{bigname}/contrib/splunk/listEmail.conf-default %{buildroot}%{_datadir}/%{bigname}/contrib/splunk/listEmail.conf
cp -pr %{buildroot}%{_datadir}/%{bigname}/template-default %{buildroot}%{_datadir}/%{bigname}/template
cp -p %{buildroot}%{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php-default  %{buildroot}%{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php
sed -i 's|\/var\/www\/html\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/expire.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/rbldns/exportdns.php
sed -i 's|\/var\/www\/html/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_datadir}/%{bigname}/contrib/splunk/webhook/readPost.php
##File list
find %{buildroot}%{_datadir}/%{bigname} -mindepth 1 -type f | grep -v '*.conf' | grep -v _config.yml | grep -v .git | grep -v '*-default' | grep -v 'ipImap/report/*.html' | grep -v config.php | grep -v 'template/' | grep -v 'contrib/rbldns/conf.default' | grep -v '*.spec' | sed -e "s@$RPM_BUILD_ROOT@@" > FILELIST
##Composer requirement
composer --working-dir="%{buildroot}%{_datadir}/%{bigname}" require dautkom/php.ipv4

%post
%if %systemd
%systemd_post rbl-expire.timer
%endif

%preun
%if %systemd
%systemd_preun %{upname}-*.service
%systemd_preun %{upname}-*.timer
%endif

%postun
%if %systemd
%systemd_postun_with_restart %{upname}-expire.timer
%endif

%files -f FILELIST
%license LICENSE
%doc doc
%config(noreplace) %{_datadir}/%{bigname}/config.php
%config(noreplace) %{_sysconfdir}/httpd/conf.d/%{bigname}.conf
%config(noreplace) %{_datadir}/%{bigname}/imap.conf
%config(noreplace) %{_datadir}/%{bigname}/notifyDomains.conf
%config(noreplace) %{_datadir}/%{bigname}/contrib/splunk/listEmail.conf
%config(noreplace) %{_datadir}/%{bigname}/contrib/amavis/exportAmavisLdap.php
%config(noreplace) %{_datadir}/%{bigname}/template/mailWarnHeaders.eml
%config(noreplace) %{_datadir}/%{bigname}/template/mailWarn.eml
%config(noreplace) %{_datadir}/%{bigname}/contrib/rbldns/conf.default

%changelog
* Mon Nov 20 2017 Marco Favero <marco.favero@csi.it> - Initial version
- Build for 2.1 official version

