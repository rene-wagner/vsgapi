reset:
	rm -rf public/antraege/* public/uploads/*
	symfony console doctrine:database:drop --force --if-exists
	symfony console doctrine:database:create
	symfony console doctrine:migrations:migrate 'DoctrineMigrations\Version20260605094811'  --no-interaction
	symfony console app:media:import-folder ../vsgdata --apply
	symfony console doctrine:migrations:migrate --no-interaction
