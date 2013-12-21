DIR=./
MODULES_DIR=$(DIR)node_modules/
BACKBONE_DIR=$(MODULES_DIR)backbone/
UNDERSCORE_DIR=$(BACKBONE_DIR)node_modules/underscore/
PUBLIC_DIR=$(DIR)public/
ASSETS_DIR=$(PUBLIC_DIR)assets/
JS_DIR=$(ASSETS_DIR)js/

BACKBONE_DEST=$(JS_DIR)/backbone-min.js

backbone:
	@echo "Building Backbone and its dependencies.";
	@cp $(BACKBONE_DIR)backbone-min.map \
		-t $(JS_DIR);
	@cat $(UNDERSCORE_DIR)underscore-min.js > \
		$(BACKBONE_DEST);
	@echo "\n" >> $(BACKBONE_DEST);
	@cat $(BACKBONE_DIR)backbone-min.js >> \
		$(BACKBONE_DEST);
	@echo "Backbone build has been completed.";

all: backbone

.PHONY: backbone
