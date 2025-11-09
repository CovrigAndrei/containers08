pipeline {
    agent {
        label 'php-agent'
    }

    environment {
        DB_PATH = '/var/www/db/db.sqlite'
    }

    stages {
        stage('Checkout') {
            steps {
                echo 'Clonarea proiectului din GitHub...'
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                echo 'Construirea imaginii Docker pentru aplicația PHP...'
                sh 'docker build -t containers08-app .'
            }
        }

        stage('Run Tests') {
            steps {
                echo 'Rularea testelor unitare din /tests/tests.php...'
                // pornim containerul cu imaginea tocmai creată
                sh '''
                    docker run --rm -v $(pwd)/tests:/var/www/tests containers08-app \
                    php /var/www/tests/tests.php
                '''
            }
        }
    }

    post {
        always {
            echo 'Pipeline încheiat.'
        }
        success {
            echo 'Toate etapele s-au executat cu succes!'
        }
        failure {
            echo 'Au apărut erori în pipeline.'
        }
    }
}
