#!/bin/bash
echo -e "\n\e[33m Installing systemd script & timers... \e[39m"
cp -pr *.service /usr/lib/systemd/system
cp -pr *.timer /usr/lib/systemd/system
echo -e "\e[33m Done! You can proceed to \e[32menable\e[39m the services you whish.\n"
