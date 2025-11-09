pipeline {
    agent {
        label 'php-agent'
    }
    
    environment {
        // Setări pentru mediu
        COMPOSER_ALLOW_SUPERUSER = 1
    }
    
    stages {
        stage('Checkout') {
            steps {
                // Checkout codul din repository
                checkout scm
                echo 'Codul a fost extras cu succes din repository'
            }
        }
        
        stage('Build Docker Image') {
            steps {
                // Construirea imaginii Docker
                echo 'Construiesc imaginea Docker...'
                sh 'docker build -t containers08 .'
            }
        }
        
        stage('Prepare Database') {
            steps {
                // Pregătirea bazei de date
                echo 'Pregătesc baza de date...'
                sh '''
                    docker create --name test-container --volume database:/var/www/db containers08
                    docker start test-container
                    sleep 5
                '''
            }
        }
        
        stage('Run Tests') {
            steps {
                // Rularea testelor
                echo 'Rulez testele...'
                sh '''
                    docker cp ./tests test-container:/var/www/html
                    docker exec test-container php /var/www/html/tests/tests.php
                '''
            }
        }
        
        stage('Cleanup') {
            steps {
                // Curățarea containerelor temporare
                echo 'Curăț containerele temporare...'
                sh '''
                    docker stop test-container || true
                    docker rm test-container || true
                '''
            }
        }
    }
    
    post {
        always {
            // Curățare finală indiferent de rezultat
            sh '''
                docker stop test-container || true
                docker rm test-container || true
            '''
            echo 'Pipeline finalizat.'
        }
        success {
            echo '✅ Toate etapele au fost finalizate cu succes!'
            // Aici poți adăuga notificări sau deploy
        }
        failure {
            echo '❌ Eroare detectată în pipeline.'
            // Aici poți adăuga notificări de eșec
        }
    }
}
