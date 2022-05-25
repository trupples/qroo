# BEST QR code management and analytics service

## Why?

[Local BEST Group Cluj-Napoca] is a student organisation founded in 1995,
affiliated to the [Technical University of Cluj-Napoca], and which is
part of the much greater international
[BEST (Board of European Students of Engineering)] organisation. Each year we
organize numerous events dedicated to developing students, both local
([CodeRun], [JobShop], [Best Training Week], [BattleLab Robotica], etc) as well
as international ([European Best Engineering Competition], [BEST Courses]).

In the promotion phase of each event, we usually distribute printed materials
with QR codes in various locations (multiple campuses, dorms, university
buildings) and in multiple formats (hand-out flyers, posters, large publicity
panels). Usually, all QR codes for a given event are identical, regardless of
the distribution medium or location. This leads to an inefficiency due to a lack
of data on the performance of each of the aforementioned advertising media. A
proposed solution is creating multiple QR codes for each event, to differentiate
between participants joining from different locations and different ad formats.

This application offers an easy to use and easy to maintain service for
generating and managing related dynamic QR codes, as well as measuring
anonymized analytics on them.

## Privacy by design

The analytics collected on the server MUST NOT contain any personal information.
By design, this software is only interested in anonymous "click count"
statistics. The only stored client state is a cookie that detects if a certain
request is the first time a user scans a given QR code or not, so as to
distinguish "unique visitors" and "total visits" for each QR code. This is not
personal information and as such requires no disclaimer or messy paperwork.

The server does store authentication data for the management interface, but the
data is minimal (email + hashed&salted password) and, while the email is
personally identifiable data, the authentication service cannot possibly
function without it, and creating an account is conditioned by having this data.

## Navigating the repo

- `schema/` - contains database schema, default entries, as well as sample data for testing
- `include/` - internal PHP functions
- `web/` - files visible to the public

## Access Control

The URLs corresponding to QR redirects (`/index.php?<code>`, encoded compactly
as `https://host?<code>`) are unauthenticated; visiting them leads to an
immediate redirect to the configured URL and to this visit event being recorded
in the Scans table.

All other URLs are dedicated to the management interface and as such are
authenticated. All management functionality is available to all logged in
users, as the internal data is not considerably sensitive and there is no point
in separating each department's scope. Login access is restricted to members of
the organisation.

**THIS IS PROBABLY NOT A SANE SECURITY MODEL FOR OTHER USES OF THIS SOFTWARE!**

User accounts cannot be created by the general public and must be managed by
users with the administrator role. They may create, delete, or reset the
password of any account.

## TODO

[ ] Log add/modify/remove actions and display them on /log.php
[ ] User creation and management interface
[ ] Update medium description

[Local BEST Group Cluj-Napoca]: https://bestcj.ro
[Technical University of Cluj-Napoca]: https://utcluj.ro
[BEST (Board of European Students of Engineering)]: https://best.eu.org
[CodeRun]: https://coderun.bestcj.ro
[JobShop]: https://jobshop.ro
[Best Training Week]: https://btw.bestcj.ro
[BattleLab Robotica]: https://battlelab.ro
[European Best Engineering Competition]: https://ebec.bestcj.ro
[BEST Courses]: https://best.eu.org/courses
