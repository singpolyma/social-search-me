/* mnu.c by Stephen Paul Weber */
/* GPL */

#include "mnu.h"
#include "categories.h"

void add_to_cat(char *cat, MenuItem *item, BOOL acat) {
	int i;
	Menu *new_node;
	DYNAMIC_STRUCT(new_node);
	new_node->item = item;
	for(i = 0; i < MAIN_CATEGORY_COUNT; i++) {
		if(strcmp(cat,MainCategories[i]) == 0) {
			if(MainCategoryLists[i] != NULL) MainCategoryLists[i]->previousNode = new_node;
			new_node->nextNode = MainCategoryLists[i];
			MainCategoryLists[i] = new_node;
			return;
		}
	}
	if(acat) {
		for(i = 0; i < ADDITIONAL_CATEGORY_COUNT; i++) {
			if(strcmp(cat,AdditionalCategories[i]) == 0) {
/*				item->hasAdditionalCategory = TRUE;*/
				if(AdditionalCategoryLists[i] != NULL) AdditionalCategoryLists[i]->previousNode = new_node;
				new_node->nextNode = AdditionalCategoryLists[i];
				AdditionalCategoryLists[i] = new_node;
				return;
			}
		}
	}
}

int file_select(struct direct *entry) {/* do a string comparison so that only .desktop files will be selected - probably should actually do this based on mime type */
	char *ptr = rindex(entry->d_name, '.');
	if(ptr != NULL && (strcmp(ptr, ".desktop") == 0))
		return TRUE;
	return FALSE;
}

void parse_desktop_file(char *path, BOOL acat) {
	char line[100], *key, *val, *cat;
	FILE *fp = fopen(path,"r");
	MenuItem *this_item;
	DYNAMIC_STRUCT(this_item);
	while(fgets(line,100,fp)) {
		key = strtok(line,"=");
		val = strtok(NULL,"=\n");
		if(strcmp(key,"Name") == 0) {
			DYNAMIC_STRING_COPY(this_item->title,val);
		}
		if(strcmp(key,"Exec") == 0) {
			DYNAMIC_STRING_COPY(this_item->command,val);
		}
/*		if(strcmp(key,"Icon") == 0)*/
		if(strcmp(key,"Categories") == 0) {
			cat = strtok(val,";");
			while(cat != NULL) {
				add_to_cat(cat,this_item, acat);
				cat = strtok(NULL,";");
			}/* end while */
		}/* end if Categories */
	}
	fclose(fp);
}

char** get_xdg_data_dirs() {
	char *tmp, *xdg_env = getenv("XDG_DATA_DIRS"), **xdg_data_dirs;
	if((xdg_data_dirs = calloc(MAX_XDG_DATA_DIRS,sizeof(char*))) == NULL) eprint("Memory allocation error.\n");
        if(!xdg_env || !xdg_env[0]) {/* if there in no envirenment variable, set defaults */
                xdg_data_dirs[0] = "/usr/local/share/applications/";
                xdg_data_dirs[1] = "/usr/share/applications/";
        } else {/* otherwise, parse environment variable */
                int i = 0;
                tmp = strtok(xdg_env,":");/* Start tokenization */
                while (tmp != NULL && i < MAX_XDG_DATA_DIRS) {/* while there is a token and we have not exceeded our directory scan maximum */
                        xdg_data_dirs[i] = strcat(tmp,"applications/");/* data_dir is this token + applications/ */
                        tmp = strtok(NULL,":");/* Get next token */
                        i++;
		}
	}
}

void read_xdg_menu(BOOL acat) {
	/* TODO: come up with a way to not put items on main categories if they hasAdditionalCategory, simply not displaying fails because the list is still being traversed */
	int i, i2, fileCount;
	struct direct **files;
	char tmp[255], **xdg_data_dirs;
	xdg_data_dirs = get_xdg_data_dirs();
        for(i = 0; i < MAX_XDG_DATA_DIRS; i++) {
                if(xdg_data_dirs[i] == NULL || !xdg_data_dirs[i][0]) continue;
                fileCount = scandir(xdg_data_dirs[i], &files, (void*)file_select, alphasort);
                for(i2 = fileCount-1; i2 > 0; i2--) {
                        strcpy(tmp,xdg_data_dirs[i]);
                        strcat(tmp,files[i2]->d_name);
                        parse_desktop_file(tmp,acat);
                }
        }
        free(files);
	free(xdg_data_dirs);
}

void draw_menu(BOOL acat) {
	Menu *curr = current_menu_head;
	if(curr == NULL) {/* draw the main menu */
		int i;
		for(i = 0; i < MAIN_CATEGORY_COUNT; i++) {
			if(i == current_menu_category)
				printf("[*] %s\n",MainCategories[i]);
			else
				printf("[ ] %s\n",MainCategories[i]);
		}
	} else {
		while(curr != NULL) {
			if(curr == current_menu_item)
				printf("[*] %s\n",curr->item->title);
			else
				printf("[ ] %s\n",curr->item->title);
			curr = curr->nextNode;
		}
	}
}

void menu_next() {
	if(current_menu_head == NULL) {/* if we are on the main menu */
		current_menu_category++;
		if(current_menu_category >= MAIN_CATEGORY_COUNT) current_menu_category = 0;
	} else {
		current_menu_item = current_menu_item->nextNode;
		if(current_menu_item == NULL) current_menu_item = current_menu_head;
	}
}

void menu_prev() {
	if(current_menu_head == NULL) {/* if we are on the main menu */
		current_menu_category--;
		if(current_menu_category <= 0) current_menu_category = MAIN_CATEGORY_COUNT-1;
	} else {
		current_menu_item = current_menu_item->previousNode;
		if(current_menu_item == NULL) current_menu_item = current_menu_head;
	}
}

void menu_go() {
	if(current_menu_head == NULL) {/* if we are on the main menu */
		previous_menu_head = NULL;
		current_menu_head = current_menu_item = MainCategoryLists[current_menu_category];
	} else {
		int command_cat = atoi(current_menu_item->item->command);
		if(command_cat) {
			command_cat--;/* We store the index+1 to distinguish between the first category index and a non-category */
			if(AdditionalCategoryLists[command_cat] != NULL) {
				previous_menu_head = current_menu_head;
				current_menu_head = current_menu_item = AdditionalCategoryLists[command_cat];
			}
		} else {
			printf("\nLaunching program \"%s\"...\n",current_menu_item->item->title);
			if(fork() == 0) {
				if(fork() == 0) {
					execl("/bin/sh","/bin/sh","-c",current_menu_item->item->command,(char*)NULL);
				}
			}
			exit(0);
		}
	}
}

int main(int argc, char *argv[]) {
	int i;
	char *initial_category = NULL;
	char in;
	BOOL acat = TRUE;/* Should we bother processing additional categories, this should cause the lists not to be placed into memory, etc, as well eventually */
	for(i = 1; i < argc; i++) /* handle command line arguments */
		if(strncmp(argv[i], "-v", 3) == 0)
			eprint("mnu-"VERSION", (C)opyright 2007, Stephen Paul Weber\n");
		else if(strncmp(argv[i], "-na", 4) == 0)
			acat = FALSE;
		else if(i+1 < argc && strncmp(argv[i], "-c", 3) == 0) {
			initial_category = argv[i+1];
			break;
		} else
			eprint("usage: mnu [-v] [-na] [-c CATEGORY]\nType \"man mnu\" for more information.\n");

	read_xdg_menu(acat);
	if(acat) add_additional_categories();
	if(initial_category != NULL) {
		for(i = 0; i < MAIN_CATEGORY_COUNT; i++) {
			if(strcmp(MainCategories[i],initial_category) == 0) {
				current_menu_head = current_menu_item = MainCategoryLists[i];
				break;
			}
		}
	}
	set_input_mode();
	while(in != 'q') {
		system("clear");
		draw_menu(acat);
		read(STDIN_FILENO, &in, 1);
		if(in == 'j') menu_next();
		if(in == 'k') menu_prev();
		if(in == 'h') {
			if(current_menu_head == NULL) return 0;
			current_menu_head = current_menu_item = previous_menu_head;
			previous_menu_head = NULL;
		}
		if(in == '\n' || in == 'l') menu_go();
	}
	
	return 0;
}
