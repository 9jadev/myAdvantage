#!/usr/bin/env bash
set -e
set -o pipefail

GIT_OAUTH2_USER="${GIT_OAUTH2_USER:-oauth2}"
GIT_BRANCH="${GIT_BRANCH:-master}"
GIT_EMAIL="${GIT_EMAIL:-development@botosoft.net}"
GIT_NAME="${GIT_NAME:-Botosoft}"

ACTION="${1:-init}"
shift

VALID_ACTIONS=("checkout" "pull" "clone" "reset" "status" "init")
if [[ ! " ${VALID_ACTIONS[*]} " == *" $ACTION "* ]]; then
    printf '>>ERROR: invalid action %s \nAction is the first argument and can only be any of:\n %s\n' "$ACTION" "$(IFS=, ; echo "${VALID_ACTIONS[*]}")" >&2
    exit 1
fi

while :; do
    case $1 in
    -b|--branch)
        if [ "$2" ]; then
            GIT_BRANCH=$2
            shift
        else
            printf 'ERROR:  %s is a non-empty optional argument\n' "$1" >&2
            exit 1
        fi
        ;;
    -u|--url)
        if [ "$2" ]; then
            FULL_GIT_URL=$2
            shift
        else
            printf 'ERROR:  %s is a non-empty optional argument\n' "$1" >&2
            exit 1
        fi
        ;;
    --)              # End of all options.
        shift
        break
        ;;
    -?*)
        printf 'ERROR: Unknown option : %s\n' "$1" >&2
        exit 1
        ;;
    *)               # Default case: No more options, so break out of the loop.
        break
    esac
shift
done

if [ -z "$APP_WORKSPACE" ]; then
  echo ">>Need to set APP_WORKSPACE env variable";exit 1
fi

if [ -z "$FULL_GIT_URL" ]; then
  if [ -z "$REPOSITORY_URL" ]; then
    echo ">>Need to set REPOSITORY_URL env variable or provide -u argument"
    exit 1
  elif [  -z "$GIT_OAUTH2_TOKEN"  ]; then
    echo ">>Need to set GIT_OAUTH2_TOKEN env variable or provide -u argument"
    exit 1
  else
    FULL_GIT_URL="https://${GIT_OAUTH2_USER}:${GIT_OAUTH2_TOKEN}@${REPOSITORY_URL}"
  fi
fi

checkout ()
{
  if [ -d "$WORKING_DIR/$APP_WORKSPACE/.git" ]; then
    printf ">>Checking out to branch [%s]:\n" "$GIT_BRANCH"
    cd "$WORKING_DIR/$APP_WORKSPACE";git checkout $GIT_BRANCH;
    git config --global user.email "$GIT_EMAIL"
    git config --global user.name "$GIT_NAME"
  else
    printf ">>The [%s] repo is not cloned.\n" "$APP_WORKSPACE"
  fi
}

pull ()
{
  echo ">>Changing directory to $WORKING_DIR/$APP_WORKSPACE"
  cd "$WORKING_DIR/$APP_WORKSPACE" || exit 1
  echo ">>Pulling latest changes from branch $GIT_BRANCH into $WORKING_DIR/$APP_WORKSPACE"
  git config --global user.email "$GIT_EMAIL"
  git config --global user.name "$GIT_NAME"
  git pull --no-ff $FULL_GIT_URL $GIT_BRANCH
  checkout
}

clone ()
{
  echo ">>Changing directory to $WORKING_DIR/$APP_WORKSPACE"
  cd "$WORKING_DIR/$APP_WORKSPACE";
  echo ">>Cloning fresh repository..."
  git clone $FULL_GIT_URL .
  if [ -d "$WORKING_DIR/$APP_WORKSPACE/.git" ]; then
    echo ">>Successfull clone $REPOSITORY_URL"
    checkout
  else
    echo ">>Error occurred, could not clone $REPOSITORY_URL, Exiting..."
    exit 1
  fi
}

init ()
{
  if [ -d "$WORKING_DIR/$APP_WORKSPACE" ]; then
    if [ -d "$WORKING_DIR/$APP_WORKSPACE/.git" ]; then
      echo ">>Repository $REPOSITORY_URL already exist"
      pull
    else
      echo ">>Workspace $APP_WORKSPACE already exist but not a git repository; removing old Workspace."
      cd "$WORKING_DIR/$APP_WORKSPACE" && rm -rf ..?* .[!.]* *
      clone
    fi
  else
    mkdir -p "$WORKING_DIR/$APP_WORKSPACE"
    clone
  fi
}

reset ()
{
  cd "$WORKING_DIR/$APP_WORKSPACE" || echo ">>Directory $WORKING_DIR/$APP_WORKSPACE does not exits" && exit 1
  git reset --hard HEAD
  git checkout $GIT_BRANCH
  git reset --hard "origin/$GIT_BRANCH"
  git pull
}

status ()
{
  if [ -d "$WORKING_DIR/$APP_WORKSPACE/.git" ]; then
    printf ">>Git status for [%s]:\n" "$APP_WORKSPACE"
    cd "$WORKING_DIR/$APP_WORKSPACE";git status;
  else
    printf "The [%s] repo is not cloned.\n" "$APP_WORKSPACE"
  fi
}

if [ "$ACTION" == "checkout" ]; then
  checkout
elif [ "$ACTION" == "clone" ]; then
  clone
elif [ "$ACTION" == "pull" ]; then
  pull
elif [ "$ACTION" == "reset" ]; then
  read -p ">>This will override any uncommited changes in your local git checkouts. Would you like to proceed? [y/n] " -r
  if [[ $REPLY =~ ^[Yy]$ ]]; then
    reset
  fi
elif [ "$ACTION" == "status" ]; then
  status
elif [ "$ACTION" == "init" ]; then
  init
fi
