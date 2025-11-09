// Jenkinsfile
pipeline {
    agent {
        label 'php-agent' 
    }

    environment {
        IMAGE_NAME = 'containers08'
        CONTAINER_NAME = 'jenkins-test-container'
        DB_VOLUME = 'jenkins_db_volume'
    }

    stages {
        stage('Checkout') {
            steps {
                echo 'Descărcare cod sursă din Git...'
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                echo 'Construire imagine Docker...'
                sh "docker build -t ${IMAGE_NAME} ."
            }
        }

        stage('Prepare Database Volume') {
            steps {
                echo 'Creare volum persistent pentru baza de date (dacă nu există)...'
                // Creează volumul dacă nu există deja
                sh """
                docker volume inspect ${DB_VOLUME} > /dev/null 2>&1 || \
                docker volume create ${DB_VOLUME}
                """
            }
        }

        stage('Run Container & Initialize DB') {
            steps {
                echo 'Pornire container și inițializare baza de date...'
                script {
                    // Oprim containerul dacă rulează deja (evităm conflicte)
                    sh "docker rm -f ${CONTAINER_NAME} || true"

                    // Creăm și pornim containerul
                    sh """
                    docker create --name ${CONTAINER_NAME} \
                        --volume ${DB_VOLUME}:/var/www/db \
                        ${IMAGE_NAME}
                    """

                    // Pornim containerul
                    sh "docker start ${CONTAINER_NAME}"

                    // Așteptăm puțin ca SQLite să creeze fișierul db.sqlite
                    sh 'sleep 5'
                }
            }
        }

        stage('Copy Tests to Container') {
            steps {
                echo 'Copiere teste în container...'
                sh "docker cp ./tests ${CONTAINER_NAME}:/var/www/html/"
            }
        }

        stage('Run PHP Tests') {
            steps {
                echo 'Rulare teste PHP...'
                script {
                    def testOutput = sh(
                        script: "docker exec ${CONTAINER_NAME} php /var/www/html/tests/tests.php",
                        returnStdout: true
                    ).trim()
                    echo testOutput

                    // Verificăm dacă toate testele au trecut
                    if (testOutput.contains('7 / 7')) {
                        echo 'TOATE TESTELE AU TRECUT!'
                    } else {
                        error 'Unele teste au eșuat!'
                    }
                }
            }
        }
    }

    post {
        always {
            echo 'Curățare resurse...'
            sh "docker stop ${CONTAINER_NAME} || true"
            sh "docker rm ${CONTAINER_NAME} || true"
        }
        success {
            echo 'Pipeline finalizat cu SUCCES!'
        }
        failure {
            echo 'Pipeline a eșuat.'
        }
    }
}
