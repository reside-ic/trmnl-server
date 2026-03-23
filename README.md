![Coverage](https://codecov.io/gh/reside-ic/trmnl-server/branch/main/graph/badge.svg)

# Reside trmnl-server

Various away-days indicated a need in the department to be more aware of
what's happening, and communicate better things like...

* What to do if you're new.
* What seminar/events are happening today/this week.
* Celebrate a paper being published.
* Say hello/goodbye to people.

As a technical solution, we acquired four TRMNL devices, which are
e-paper based, using extremely low power so can run for long
periods (months) without needing charging. They can pull images
from a very simple server we can host. This repo is all about them.

# Deploying the server

```
python deploy.py
```

will pull some secrets from the vault - particularly the MAC addresses 
and friendly names of our devices so far. These get written to the 
`secret/` folder, which is protected from browsing.

# Deploying a device

## Building.

The kit is [here](https://wiki.seeedstudio.com/trmnl_7inch5_diy_kit_main_page/),
with instructions which are very simple, although the ribbon cable connecting
the screen to the board is fiddly. If the screen, board, and ribbon cable
writing are all face up, some careful squishing should do it.

The cases require 3-D printing - I used the wall0-mount version of 
[this](https://www.printables.com/model/1577845-trmnl-75inchog-diy-case-with-magnets-or-wallmount/files)
and needed some M3 0.8mm screws.

## First connection.

* Switch on a single device, and use a laptop to connect to the `TRMNL` 
wireless network it serves.
* A login portal should appear, with the MAC address on the bottom.
* Add the device to vault with something like:-

```
vault write /secret/trmnl/device5 mac="11:22:33:44:55:66" api_key="SecretApiKey" refresh_interval=1800
```

where `device5` is a friendly name used elsewhere. Inform Chris (IT) about the new device,
as ICT will need to add the MAC address to the Imperial-PSK network. When that's done...

* In advanced settings, set the URL of the TRMNL server to `https://mrcdata.dide.ic.ac.uk/trmnl`.
* The TRMNL wants to join the wifi. Select the `Imperial-PSK` option out of the wifi,
and feed the key which is in `/secret/trmnl/imperial_psk` in the vault. Click connect - 
in practise this was flaky and normally worked on the second or third attempt.
* Even after connecting, a "Malformed content" error briefly appears, before the Reside
Setup logo looks amazing in 4 grey-scale colours.

# Endpoints:

## Required for TRMNL.

* `/api/setup` - called once to register the device.
* `/api/display` - called regularly to get latest image.
* `/api/log` - called on any error.

These are the endpoints the devices are expecting, and are implemented
as per the spec [here](https://github.com/usetrmnl/trmnl-firmware),
except that I haven't implemented forcing a firmware update at present,
which can be returned alongside the latest image.

## Our own endpoints

* `/metrics` implements an endpoint for Prometheus, including the
battery voltage, wifi strength, and time of last successful refresh.

# Code, tests, CI.

No special installation is needed for PHP - it is a really simple
REST interface. All API calls are sent to `api/index.php` by the
`.htaccess` file in that folder, and are routed to the other PHP
files, one for each endpoint.

To run the tests locally, first run `fetch_test_tools.bat` in the
root folder to download the support for testing, then see `test.bat`
which will run the tests, lint, and produce a code coverage report
in `coverage_report/index.html`

See the `.github/workflows` folder for usual CI.
