## Build Plugin
##
##

NAME = wp-simplify

# Default Install Action
default:
	make install

# Build for Distribution
build:
	echo Building $(NAME).

# Install for Staging/Development
install:
	echo Installing $(NAME).
