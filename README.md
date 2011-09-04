# Dynamic Find Behavior

## Installation
	
	cd app/plugins
	git clone git://github.com/joeytrapp/Dynamic-Find-Behavior.git dynamic_find

## Usage

This behavior allows you to use more convenience methods on your models. Methods like $this->ModelName->findNameById($id), where Name can be any field or virtual field in the database table, and Id can be any field (not virtual) in the same table.

Multiple methods are available, but all can take a second parameter, which is an array of additions query information. You could pass in any of the keys you would pass into the Model::find() second parameter and they will merge with what the behavior creates.

The possible methods available are:

* findFieldNameByOtherField($condition);
* findListFieldNameByOtherField($condition);
* findAllFieldNameByOtherField($condition);

All of these methods can also be used by replacing the By with For if that makes the method name more readable.