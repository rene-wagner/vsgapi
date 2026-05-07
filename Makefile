reset:
	symfony console doctrine:database:drop --force --if-exists
	symfony console doctrine:database:create
	symfony console doctrine:migrations:migrate --no-interaction
	rm -rvf var/me* public/uploads/cropped/* public/uploads/cropped-thumbnails/* public/uploads/items/* public/uploads/thumbnails/*
	cp ~/Bilder/Vsg/* public/uploads/items/
