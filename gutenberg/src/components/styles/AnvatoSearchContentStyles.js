const allStyles = {
  anvroot: {

  },
  content: {
    container: {

    },
    inner: {
      minHeight: '50vh',
      maxHeight: '50vh',
      overflow: 'auto'
    },
    list: {
      display: 'flex',
      flexFlow: 'row wrap',
      justifyContent: 'space-between',
    },
    item: {
      container: {
        cursor: 'pointer',
        border: '0 solid transparent',
        width: '270px',
        margin: '10px',
        padding: '3px',
      },
      thumbnail: {
        image: {
          width: 'auto',
          height: '120px',
          backgroundColor: 'rgba(243, 243, 243, 1)',
          display: 'block',
          backgroundPosition: ' center center',
          backgroundSize: 'cover',
          position: 'relative',
          marginBottom: '5px'
        },
        duration: {
          position: 'absolute',
          bottom: 0,
          right: 0,
          padding: '6px',
          background: '#000',
          color: '#FFF',
          fontSize: '13px',
        }
      },
      details: {
        container: {
          textAlign: 'left',
          color: '#2b2b2b',
          padding: '10px 10px 0',
        },
        main: {
          container: {
            minHeight: '50px'
          },
          title: {
            fontWeight: 700,
            fontSize: '14px',
            color: '#222',
            lineHeight: '14px',
            display: 'block',
            padding: '3px 0',
          },
          description: {
            display: 'block',
            color: '#565656',
            lineHeight: '13px',
            fontSize: '13px',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
          }
        },
        meta: {
          item: {
            fontSize: '12px',
            lineHeight: '14px',
            margin: '5px 0',
            minWidth: '60px',
            position: 'relative',
          },
          label: {
            textTransform: 'capitalize',
            marginRight: '2px',
            fontWeight: '700',
            minWidth: '60px',
            position: 'relative',
          },
          value: {
            textTransform: 'capitalize',
          },
        }
      },
    },
  },
  searchbar: {
    container: {
      display: 'flex',
      alignItems: 'flex-start'
    },
    item: {
      marginRight: '4px',
    },
    query: {
      width: '200px',
      marginTop: '1px'
    }
  },
  spinner: {
    display: 'flex',
    justifyContent: 'center'
  },
  noContent: {
    color: 'black',
    textAlign: 'center',
    borderRadius: '5px',
    padding: '2px',
  },
  footer: {
    container: {
      display: 'flex',
    },
    insertIntoPost: {
      marginLeft: 'auto'
    }
  }
}

export const styles = allStyles;