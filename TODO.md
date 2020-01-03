+ [x] Instead of managing multiple indexes for various object types, considers adding a filter functionality to the
      index allowing to filter the index objects by type (and by other criteria)
+ [x] Add sort functionality
+ [x] Filter only matching objects
+ [x] Elegant and standardized way to retrieve specific matcher value from a searchable
+ [x] Document highlighter forwarding
+ [x] Instead of forwarding highlighter to value and matcher, just forward and assign to value and read highlighter
      in AbstractMatcher::match() from the value object and pass as argument to AbstractMatcher::matchValue() (or make
      it accessible there in another way)
