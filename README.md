# group_assign
Assign Tasks to Groups

# Not fully working, but doesn't break anything so far. Please Beta Test.

* ~~Need to isolate user lookups in kanboard~~
* ~~Need to add db scheme~~
* ~~Need to add group name relationship to getdetails in TaskFinderModel~~
* ~~Need to override templates~~
* ~~Need to override Helpers~~
* ~~Need to override Models~~
* Need to somehow show Assigned Group in the Boards Task Renders
* ~~Need to look at dashboard query~~
* ~~Need to add filters~~

# Progress 4.13.18
* A task can now have an assigned group, testing appears to correctly select, modify, and store the correct group.
* If a user is in a group that a task is assigned to, it will show up on their dashboard.
* using ``assignee:me`` in filter will find tasks assigned to groups that the user is in.
* using ``assignee:'GroupName'`` in filter will find tasks assigned to a group by NAME of the group.
* using ``assignee:`GroupID'`` in filter will find tasks assigned to a group by ID number of group.
* using ``assignee:'Username'`` or ``assignee:'Name'`` will NOT find a task assigned to a group with that UserName or Name in the group, sorry...someone else can figure that one out.
