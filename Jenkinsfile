pipeline {
    agent {
        label 'php-agent' // agentul configurat anterior
    }

    environment {
        DB_PATH = '/var/www/db/db.sqlite'
    }

    stages {

        stage('Checkout') {
            steps {
                echo 'Clonarea codului sursă din GitHub...'
                checkout scm
            }
        }

        stage('Prepare Environment') {
            steps {
                echo 'Pregătirea mediului PHP...'
                sh '''
                    php -v
                    echo "Verificarea structurii proiectului..."
                    ls -R site
                '''
            }
        }

        stage('Database Setup') {
            steps {
                echo 'Crearea bazei de date SQLite...'
                sh '''
                    if [ ! -f $DB_PATH ]; then
                        mkdir -p /var/www/db
                        cat sql/schema.sql | sqlite3 $DB_PATH
                        chmod 777 $DB_PATH
                        echo "Baza de date creată cu succes."
                    else
                        echo "Baza de date există deja."
                    fi
                '''
            }
        }

        stage('Run Tests') {
            steps {
                echo 'Rularea testelor unitare...'
                sh '''
                    php tests/tests.php
                '''
            }
        }
    }

    post {
        always {
            echo 'Pipeline finalizat.'
        }
        success {
            echo 'Toate etapele s-au finalizat cu succes!'
        }
        failure {
            echo 'A apărut o eroare în timpul execuției pipeline-ului.'
        }
    }
}
