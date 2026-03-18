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

`python deploy.py` to pull some secrets from the vault - particularly
the MAC addresses and friendly names of our devices so far. These get
written to the `secret/` folder, which is protected from browsing.

