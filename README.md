## Checkout our latest project
[![](https://raw.githubusercontent.com/docpht/docpht/master/public/assets/img/logo.png)](https://github.com/docpht/docpht)

- With [DocPHT](https://github.com/docpht/docpht) you can take notes and quickly document anything and without the use of any database.
-----------
[![Latest release](https://img.shields.io/github/release/creecros/Group_assign.svg)](https://github.com/creecros/Group_assign/releases)
[![GitHub license](https://img.shields.io/github/license/Naereen/StrapDown.js.svg)](https://github.com/creecros/Group_assign/blob/master/LICENSE)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/creecros/Group_assign/graphs/contributors)
[![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)]()
[![Downloads](https://img.shields.io/github/downloads/creecros/Group_assign/total.svg)](https://github.com/creecros/Group_assign/releases)

Donate to help keep this project maintained.
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SEGNEVQFXHXGW&source=url">
<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" /></a>

:star: If you use it, you should star it on Github! 
It's the least you can do for all the work put into it!


# Group_assign
Assign Tasks to Groups or from Multi-Select of Users with permissions from the project

# Requirements
Kanboard v1.1.0 or Higher

Group_assign Versions 1.7.7 and below will support PHP version below 7

# Features and usage
* A task can have an assigned group or selection of users
* Can only assign groups or other assigness to a task that have permissions in the Project.
* If a user is in a group that a task is assigned to, it will show up on their dashboard.
* If a user is in other assignees multiselect that a task is assigned to, it will show up on their dashboard.
* If a user is in a group that a task is assigned to, it will show up in their calendar.
* If a user is in other assignees multiselect that a task is assigned to, it will show up in their calendar.
* If a group is assigned or a user is assigneed in other assignees, it will be appear on the task in detail view, board view, creation, modification. 
* Includes 5 Automatic Actions to utilize the Assigned Group
  * Email Assigned Group on Task Modification, Creation, Close, or Movement
  * Email Assigned Group of impending Task Due Date
  * Email Other Assignees on Task Modification, Creation, Close, or Movement
  * Email Other Assignees of impending Task Due Date
  * Assign task to a group on creation or movement
* using ``allassignees:me`` (``assignee:me`` for pre 1.7.3 versions) in filter will find tasks assigned to groups that the user is in or assignee in other assignees is in.
* using ``allassignees:GroupName`` (``assignee:GroupName`` for pre 1.7.3 versions) in filter will find tasks assigned to a group by NAME of the group.
* using ``allassignees:GroupID`` (``assignee:GroupID`` for pre 1.7.3 versions) in filter will find tasks assigned to a group by ID number of group.
* using ``allassignees:Username`` or ``allassignees:Name`` will find all tasks assigned to that user regardless of how they have been assigneed, whether in the group or in Other Assignees or Assignee. 
* User assigneed via a group or multiselect will now recieve notifications
* Changing assigned group or any multiselect users will now trigger `EVENT_ASSIGNEE_CHANGE`
* Duplicating Tasks will include assigned groups and other users.
  * Duplicating to another project or moving to another project will check permissions of assignees, and remove those without permission.
* Task Reccurences will include group assigned and other assignees in the recurrence.
* Setting included to enable group managment for Application Managers
  * Found in `Settings > Application settings`

# Future enhancments
Find bugs or missing functionality, please report it.

- [x] Add a few basic automatic actions that utilize Groups assigned
- [x] Add relationship for ``allassignees:Username`` or ``allassignees:Name`` in the table lookup 
- [x] Add an event for assigned group change.
- [x] Incorporate into notifications
- [x] Address Task Duplication
- [x] Task Recurrence

# Manual Installation

- Find the release you wish to install: https://github.com/creecros/Group_assign/releases
- Download the provided zip, not the source zip, i.e. `Group_assign-x.x.x.zip`
- Unzip contents to the plugins folder

In the event that you use the master repo, ensure that the directory of the plugin is named `Group_assign`, or else the plugin will not work.

# Screenshots

## Task Details:
![image](https://user-images.githubusercontent.com/26339368/49951197-64546680-fec7-11e8-9473-82820b1a4f7e.png)

## Task Creation/Modification:
![image](https://user-images.githubusercontent.com/26339368/38753761-692db008-3f2d-11e8-8ce2-59d88ddf39b1.png)
![image](https://user-images.githubusercontent.com/26339368/49557918-3c696f80-f8d7-11e8-91b8-7cef11c6eec0.png)

## Board View:
![image](https://user-images.githubusercontent.com/26339368/49951135-3a9b3f80-fec7-11e8-9bf6-3a777c09c675.png)

## Users Calendar View

- Tasks that a user is assigned too but not main assignee will show up in calendar, with Dark Grey Background and Task color Border, to differentiate that they are not the main assignee.

![image](https://user-images.githubusercontent.com/26339368/49655821-b7cb3e00-fa09-11e8-9608-952abbf146fa.png)


## Automatic Actions:
![image](https://user-images.githubusercontent.com/26339368/38754253-0a0fd2de-3f2f-11e8-9dde-2036de011a6b.png)

![image](https://user-images.githubusercontent.com/26339368/38754279-2285d0d4-3f2f-11e8-88c2-0ed91e452f90.png)

![image](https://user-images.githubusercontent.com/26339368/38754288-310df2c6-3f2f-11e8-9993-39e96b55076c.png)

