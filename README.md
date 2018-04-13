# Group_assign
Assign Tasks to Groups

# Features and usage
* A task can have an assigned group.
* Can only assign groups to a task that have permissions in the Project.
* If a user is in a group that a task is assigned to, it will show up on their dashboard.
* If a group is assigned, it will be appear on the task in detail view, board view, creation, modification. 
* Includes 3 Automatic Actions to utilize the Assigned Group
  * Email Assigned Group on Task Modification, Creation, Close, or Movement
  * Email Assigned Group of impending Task Due Date
  * Assign task to a group on creation or movement
* using ``assignee:me`` in filter will find tasks assigned to groups that the user is in.
* using ``assignee:'GroupName'`` in filter will find tasks assigned to a group by NAME of the group.
* using ``assignee:`GroupID'`` in filter will find tasks assigned to a group by ID number of group.
* using ``assignee:'Username'`` or ``assignee:'Name'`` will NOT find a task assigned to a group with that UserName or Name in the group. 

# Future enhancments
Find bugs or missing functionality, please report it.

- [x] Add a few basic automatic actions that utilize Groups assigned
- [ ] Add relationship for ``assignee:'Username'`` or ``assignee:'Name'`` in the table lookup 
- [ ] Add an event for assigned group change.
- [ ] Incorporate into notifications
