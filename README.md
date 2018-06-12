# Group_assign
Assign Tasks to Groups

# Requirements
Kanboard v1.1.0 or Higher

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

# Screenshots

## Task Details:
![image](https://user-images.githubusercontent.com/26339368/38753714-493c926e-3f2d-11e8-8ef7-271bab0e255d.png)

## Task Creation/Modification:
![image](https://user-images.githubusercontent.com/26339368/38753761-692db008-3f2d-11e8-8ce2-59d88ddf39b1.png)

## Board View:
![image](https://user-images.githubusercontent.com/26339368/38753779-77b931d8-3f2d-11e8-8160-ef2563119252.png)

## Automatic Actions:
![image](https://user-images.githubusercontent.com/26339368/38754253-0a0fd2de-3f2f-11e8-9dde-2036de011a6b.png)

![image](https://user-images.githubusercontent.com/26339368/38754279-2285d0d4-3f2f-11e8-88c2-0ed91e452f90.png)

![image](https://user-images.githubusercontent.com/26339368/38754288-310df2c6-3f2f-11e8-9993-39e96b55076c.png)

## Calendar Plugin Updated to work with Group_assign
You can install seperately here: https://github.com/creecros/plugin-calendar

