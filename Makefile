reset:
	symfony console doctrine:database:drop --force --if-exists
	symfony console doctrine:database:create
	symfony console doctrine:migrations:migrate --no-interaction
	rm -rvf public/antraege/* public/uploads/items/* public/uploads/thumbnails/*
	cp ../vsgdata/public/uploads/items/* public/uploads/items/
	cp ../vsgdata/public/uploads/thumbnails/* public/uploads/thumbnails/
